<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ストックリスト</title>
    <link rel="stylesheet" href="css/style.css">
    <script>if (localStorage.getItem('darkMode') === 'on') document.documentElement.classList.add('dark');</script>
</head>
<body>
    <div class="container">
        <h1>ストックリスト</h1>
        <a href="index.php">← 今日のタスクへ</a>

        <form id="add-form">
            <input type="text" id="content-input" placeholder="やりたいこと・課題を入力" required autofocus>
            <button type="submit">追加</button>
        </form>

        <ul id="task-list"></ul>

        <div id="completed-section">
            <button id="completed-toggle" aria-expanded="false">
                <span>完了済み</span>
                <span class="chevron">▼</span>
            </button>
            <div id="completed-body">
                <ul id="completed-list"></ul>
                <p id="completed-empty" class="hidden">完了したタスクはありません</p>
            </div>
        </div>
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
        const list = document.getElementById('task-list');
        const completedList = document.getElementById('completed-list');
        const completedEmpty = document.getElementById('completed-empty');
        const completedToggle = document.getElementById('completed-toggle');
        const completedBody = document.getElementById('completed-body');
        const form = document.getElementById('add-form');
        const input = document.getElementById('content-input');

        completedToggle.addEventListener('click', () => {
            const expanded = completedToggle.getAttribute('aria-expanded') === 'true';
            completedToggle.setAttribute('aria-expanded', !expanded);
            completedBody.classList.toggle('expanded', !expanded);
        });

        async function loadTasks() {
            const [activeRes, completedRes] = await Promise.all([
                fetch('api/tasks.php'),
                fetch('api/tasks.php?status=completed'),
            ]);
            const activeTasks = await activeRes.json();
            const completedTasks = await completedRes.json();

            list.innerHTML = '';
            activeTasks.forEach(task => {
                const li = document.createElement('li');
                const span = document.createElement('span');
                span.textContent = task.content;
                const btn = document.createElement('button');
                btn.textContent = '削除';
                btn.addEventListener('click', () => deleteTask(task.id));
                li.appendChild(span);
                li.appendChild(btn);
                list.appendChild(li);
            });

            completedList.innerHTML = '';
            completedEmpty.classList.toggle('hidden', completedTasks.length !== 0);
            completedTasks.forEach(task => {
                const li = document.createElement('li');
                const span = document.createElement('span');
                span.textContent = task.content;
                li.appendChild(span);
                completedList.appendChild(li);
            });
        }

        async function deleteTask(id) {
            await fetch(`api/tasks.php?id=${id}`, { method: 'DELETE' });
            loadTasks();
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const content = input.value.trim();
            if (!content) return;
            await fetch('api/tasks.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content }),
            });
            input.value = '';
            loadTasks();
        });

        loadTasks();
    </script>
</body>
</html>
