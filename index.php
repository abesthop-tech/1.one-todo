<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>今日のタスク</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        if (localStorage.getItem('darkMode') === 'on') document.documentElement.classList.add('dark');
    </script>
</head>

<body>
    <div class="container">
        <header>
            <h1>今日やること</h1>
            <a href="stock.php">ストックリスト →</a>
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
                </div>
            </div>

            <div id="empty" class="state hidden">
                <p>全タスク完了！えらいぞ！</p>
                <a href="stock.php">追加するならクリック →</a>
            </div>

            <div id="completed" class="state hidden">
                <span class="complete-icon">✓</span>
                <p class="complete-msg">よく頑張りました！</p>
                <p class="complete-sub">今日の1タスク、完了です。</p>
                <div class="actions">
                    <button id="btn-next-task">次のタスクへ</button>
                </div>
            </div>
        </main>
    </div>

    <footer>© 2026 HCP4</footer>

    <div id="settings-container">
        <button id="gear-btn" aria-label="設定">⚙</button>
        <div id="settings-panel" class="hidden">
            <label class="toggle-label">
                <span>ダークモード</span>
                <span class="toggle">
                    <input type="checkbox" id="dark-toggle">
                    <span class="toggle-slider"></span>
                </span>
            </label>
        </div>
    </div>
    <script src="js/settings.js"></script>

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
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.id = `fixed-${task.id}`;
                input.dataset.id = task.id;
                input.dataset.content = task.content;
                input.addEventListener('change', checkClick);
                const label = document.createElement('label');
                label.htmlFor = `fixed-${task.id}`;
                label.textContent = task.content;
                li.appendChild(input);
                li.appendChild(label);
                list.appendChild(li);
            });
        }

        async function checkClick(e) {
            const checkbox = e.target;
            checkbox.disabled = true;
            await fetch('api/completions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    task_id: parseInt(checkbox.dataset.id),
                    content: checkbox.dataset.content,
                    reason: '',
                    is_fixed: 1,
                }),
            });
            const checkboxes = document.querySelectorAll('#tasks-list input[type=checkbox]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            if (allChecked) {
                document.getElementById('fixed-complete-msg').classList.remove('hidden');
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