<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ストックリスト</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        if (localStorage.getItem('darkMode') === 'on') document.documentElement.classList.add('dark');
    </script>
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

        <div id="fixed-section">
            <h2>必須タスク</h2>

            <div id="fixed-add-form">
                <input type="text" id="fixed-content-input" placeholder="今日やること">
                <input type="date" id="fixed-date-input">
                <button id="fixed-add-btn">追加</button>
            </div>

            <ul id="fixed-task-list"></ul>

            <div id="fixed-completed-section">
                <button id="fixed-completed-toggle" aria-expanded="false">
                    <span>完了済み（必須）</span>
                    <span class="chevron">▼</span>
                </button>
                <div id="fixed-completed-body">
                    <ul id="fixed-completed-list"></ul>
                    <p id="fixed-completed-empty" class="hidden">完了したタスクはありません</p>
                </div>
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
        const fixedTaskList = document.getElementById('fixed-task-list');
        const fixedCompletedToggle = document.getElementById('fixed-completed-toggle');
        const fixedCompletedBody = document.getElementById('fixed-completed-body');
        const fixedCompletedList = document.getElementById('fixed-completed-list');
        const fixedCompletedEmpty = document.getElementById('fixed-completed-empty');

        completedToggle.addEventListener('click', () => {
            const expanded = completedToggle.getAttribute('aria-expanded') === 'true';
            completedToggle.setAttribute('aria-expanded', !expanded);
            completedBody.classList.toggle('expanded', !expanded);
        });

        fixedCompletedToggle.addEventListener('click', () => {
            const expanded = fixedCompletedToggle.getAttribute('aria-expanded') === 'true';
            fixedCompletedToggle.setAttribute('aria-expanded', !expanded);
            fixedCompletedBody.classList.toggle('expanded', !expanded);
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

        async function loadFixedTasks() {
            const [activeRes, completedRes] = await Promise.all([
                fetch('api/tasks.php?type=fixed'),
                fetch('api/tasks.php?type=fixed&status=completed'),
            ]);
            const activeFixedTasks = await activeRes.json();
            const completedFixedTasks = await completedRes.json();

            fixedTaskList.innerHTML = '';
            activeFixedTasks.forEach(task => {
                const li = document.createElement('li');
                const dateSpan = document.createElement('span');
                const d = new Date(task.scheduled_date + 'T00:00:00');
                dateSpan.textContent = `${d.getMonth() + 1}/${d.getDate()}`;
                const span = document.createElement('span');
                span.textContent = task.content;
                const btn = document.createElement('button');
                btn.textContent = '削除';
                btn.addEventListener('click', () => deleteFixedTask(task.id));
                li.appendChild(dateSpan);
                li.appendChild(span);
                li.appendChild(btn);
                fixedTaskList.appendChild(li);
            });

            fixedCompletedEmpty.classList.toggle('hidden', completedFixedTasks.length !== 0);
            fixedCompletedList.innerHTML = '';
            completedFixedTasks.forEach(task => {
                const li = document.createElement('li');
                const span = document.createElement('span');
                span.textContent = task.content;
                li.appendChild(span);
                fixedCompletedList.appendChild(li);
            });
        }

        async function deleteTask(id) {
            await fetch(`api/tasks.php?id=${id}`, {
                method: 'DELETE'
            });
            loadTasks();
        }

        async function deleteFixedTask(id) {
            await fetch(`api/tasks.php?id=${id}`, {
                method: 'DELETE'
            });
            loadFixedTasks();
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const content = input.value.trim();
            if (!content) return;
            await fetch('api/tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    content
                }),
            });
            input.value = '';
            loadTasks();
        });

        document.getElementById('fixed-add-btn').addEventListener('click', async (e) => {
            const content = document.getElementById('fixed-content-input').value.trim();
            const date = document.getElementById('fixed-date-input').value;
            if (!content || !date) return;
            await fetch('api/tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    content,
                    is_fixed: 1,
                    scheduled_date: date
                }),
            });
            document.getElementById('fixed-content-input').value = '';
            document.getElementById('fixed-date-input').value = '';
            loadFixedTasks();
        });

        loadTasks();
        loadFixedTasks();
    </script>
</body>

</html>