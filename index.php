<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>今日のタスク</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <div class="container">
        <header>
            <p id="today-date"></p>
        </header>

        <main id="main">
            <div id="fixed-tasks" class="hidden">
                <p id="fixed-heading">今日の必須タスク</p>
                <ul id="tasks-list"></ul>
                <p id="fixed-complete-msg" class="hidden">✓ 今日の必須タスク、全完了！</p>
            </div>

            <div id="loading" class="state">考え中...</div>

            <div id="suggestion" class="state hidden">
                <p id="task-content"></p>
                <p id="task-reason"></p>
                <div class="actions">
                    <button id="btn-complete">タスクを完了する！</button>
                    <a href="stock.php" class="btn-add-task">タスクを追加・確認</a>
                </div>
            </div>

            <div id="empty" class="state hidden">
                <p>全タスク完了！えらいぞ！</p>
                <div class="actions">
                    <a href="stock.php" class="btn-add-task">タスクを追加・確認</a>
                </div>
            </div>

            <div id="completed" class="state hidden">
                <span class="complete-icon">✓</span>
                <p class="complete-msg">よく頑張りました！</p>
                <p class="complete-sub">今日の1タスク、完了です。</p>
                <div class="actions">
                    <button id="btn-next-task">次のタスクへ</button>
                    <a href="stock.php" class="btn-add-task">タスクを追加・確認</a>
                </div>
            </div>
        </main>
    </div>

    <footer>© 2026 HCP4</footer>

    <script>
        let currentTask = null;

        const states = ['loading', 'suggestion', 'empty', 'completed'];

        function showState(name) {
            states.forEach(s => {
                document.getElementById(s).classList.toggle('hidden', s !== name);
            });
        }

        async function fetchSuggestion() {
            showState('loading');
            const res = await fetch('api/suggest.php', {
                method: 'POST'
            });
            const data = await res.json();

            if (data.error === 'タスクがありません') {
                showState('empty');
                return;
            }

            currentTask = data;
            document.getElementById('task-content').textContent = data.content;
            document.getElementById('task-reason').textContent = data.reason;
            showState('suggestion');
        }

        async function loadFixedTasks() {
            const section = document.getElementById('fixed-tasks');
            const list = document.getElementById('tasks-list');

            const d = new Date();
            const today = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            const res = await fetch(`api/tasks.php?type=fixed&date=${today}`);
            const tasks = await res.json();

            list.innerHTML = '';
            if (tasks.length === 0) {
                section.classList.add('hidden');
                return;
            }
            section.classList.remove('hidden');
            tasks.forEach(task => {
                const li = document.createElement('li');
                li.dataset.id = task.id;
                li.dataset.content = task.content;
                const icon = document.createElement('span');
                icon.className = 'check-icon';
                icon.textContent = '○';
                const span = document.createElement('span');
                span.textContent = task.content;
                li.appendChild(icon);
                li.appendChild(span);
                li.addEventListener('click', checkClick);
                list.appendChild(li);
            });
        }

        async function checkClick(e) {
            const li = e.currentTarget;
            const icon = li.querySelector('.check-icon');
            const isCompleted = li.classList.contains('completed');

            if (isCompleted) {
                li.classList.remove('completed');
                icon.textContent = '○';
                document.getElementById('fixed-complete-msg').classList.add('hidden');
                await fetch(`api/tasks.php?id=${li.dataset.id}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_completed: 0 }),
                });
            } else {
                li.classList.add('completed');
                icon.textContent = '✅';
                await fetch('api/completions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        task_id: parseInt(li.dataset.id),
                        content: li.dataset.content,
                        reason: '',
                        is_fixed: 1,
                    }),
                });
                const items = document.querySelectorAll('#tasks-list li');
                const allDone = Array.from(items).every(item => item.classList.contains('completed'));
                if (allDone) {
                    document.getElementById('fixed-complete-msg').classList.remove('hidden');
                }
            }
        }

        document.getElementById('today-date').innerHTML = new Date().toLocaleDateString('ja-JP',{
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'short',
        });

        document.getElementById('btn-complete').addEventListener('click', async () => {
            if (!currentTask) return;
            const task = currentTask;
            currentTask = null;
            const suggestionEl = document.getElementById('suggestion');
            suggestionEl.classList.add('completing');
            await Promise.all([
                fetch('api/completions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        task_id: task.id,
                        content: task.content,
                        reason: task.reason,
                    }),
                }),
                new Promise(r => setTimeout(r, 400)),
            ]);
            suggestionEl.classList.remove('completing');
            document.getElementById('btn-next-task').classList.remove('btn-clicked');
            showState('completed');
        });

        document.getElementById('btn-next-task').addEventListener('click', () => {
            const btn = document.getElementById('btn-next-task');
            btn.classList.add('btn-clicked');
            setTimeout(fetchSuggestion, 240);
        });

        loadFixedTasks()
        fetchSuggestion();
    </script>
</body>

</html>