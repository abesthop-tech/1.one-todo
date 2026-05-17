<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>今日のタスク</title>
    <link rel="stylesheet" href="css/style.css">
    <script>if (localStorage.getItem('darkMode') === 'on') document.documentElement.classList.add('dark');</script>
</head>
<body>
    <div class="container">
        <header>
            <h1>今日やること</h1>
            <a href="stock.php">ストックリスト →</a>
        </header>

        <main id="main">
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
            const res = await fetch('api/suggest.php', { method: 'POST' });
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

        document.getElementById('btn-complete').addEventListener('click', async () => {
            if (!currentTask) return;
            const task = currentTask;
            currentTask = null;
            const suggestionEl = document.getElementById('suggestion');
            suggestionEl.classList.add('completing');
            await Promise.all([
                fetch('api/completions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        task_id: task.id,
                        content: task.content,
                        reason:  task.reason,
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

        fetchSuggestion();
    </script>
</body>
</html>
