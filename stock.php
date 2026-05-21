<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ストックリスト</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <div class="container">
        <header class="stock-header">
            <a href="index.php" id="btn-back">← 今日のタスクへ</a>
        </header>

        <div id="task-add-section">
            <div id="task-add-row">
                <input type="text" id="add-content-input" placeholder="やりたいこと・課題を入力" autofocus>
                <label class="type-toggle">
                    <span class="type-label">通常</span>
                    <span class="toggle">
                        <input type="checkbox" id="is-fixed-toggle">
                        <span class="toggle-slider"></span>
                    </span>
                    <span class="type-label">必須</span>
                </label>
            </div>
            <input type="date" id="add-date-input" class="hidden">
            <button id="add-task-btn">追加</button>
        </div>

        <ul id="task-list"></ul>

        <div id="completed-section">
            <button id="completed-toggle" aria-expanded="false">
                <span>完了済み</span>
                <span class="chevron">▼</span>
            </button>
            <div id="completed-body">
                <div id="completed-filter">
                    <select id="filter-type">
                        <option value="all">すべて</option>
                        <option value="0">通常のみ</option>
                        <option value="1">必須のみ</option>
                    </select>
                    <input type="date" id="filter-date" class="hidden">
                </div>
                <ul id="completed-list"></ul>
                <p id="completed-empty" class="hidden">完了したタスクはありません</p>
            </div>
        </div>
    </div>

    <footer>© 2026 HCP4</footer>

    <script>
        const taskList = document.getElementById('task-list');
        const completedList = document.getElementById('completed-list');
        const completedEmpty = document.getElementById('completed-empty');
        const completedToggle = document.getElementById('completed-toggle');
        const completedBody = document.getElementById('completed-body');
        const isFixedToggle = document.getElementById('is-fixed-toggle');
        const addDateInput = document.getElementById('add-date-input');
        const filterType = document.getElementById('filter-type');
        const filterDate = document.getElementById('filter-date');

        function todayStr() {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        }

        completedToggle.addEventListener('click', () => {
            const expanded = completedToggle.getAttribute('aria-expanded') === 'true';
            completedToggle.setAttribute('aria-expanded', !expanded);
            completedBody.classList.toggle('expanded', !expanded);
        });

        isFixedToggle.addEventListener('change', () => {
            const show = isFixedToggle.checked;
            addDateInput.classList.toggle('hidden', !show);
            if (show && !addDateInput.value) addDateInput.value = todayStr();
            loadActiveTasks();
        });

        filterType.addEventListener('change', () => {
            const show = filterType.value !== '0';
            filterDate.classList.toggle('hidden', !show);
            if (show && !filterDate.value) filterDate.value = todayStr();
            loadCompleted();
        });

        filterDate.addEventListener('change', loadCompleted);

        [addDateInput, filterDate].forEach(el => {
            el.addEventListener('click', () => { try { el.showPicker(); } catch(e) {} });
        });

        async function loadActiveTasks() {
            const isFixed = isFixedToggle.checked;
            const res = await fetch(isFixed ? 'api/tasks.php?type=fixed' : 'api/tasks.php');
            const tasks = await res.json();

            taskList.innerHTML = '';
            tasks.forEach(task => {
                const li = document.createElement('li');
                if (isFixed) {
                    const dateSpan = document.createElement('span');
                    dateSpan.className = 'date-label';
                    const d = new Date(task.scheduled_date + 'T00:00:00');
                    dateSpan.textContent = `${d.getMonth() + 1}/${d.getDate()}`;
                    li.appendChild(dateSpan);
                }
                const span = document.createElement('span');
                span.textContent = task.content;
                const btn = document.createElement('button');
                btn.textContent = '削除';
                btn.addEventListener('click', () => deleteTask(task.id));
                li.appendChild(span);
                li.appendChild(btn);
                taskList.appendChild(li);
            });
        }

        async function loadCompleted() {
            const type = filterType.value;
            const date = filterDate.value;

            let url = 'api/tasks.php?status=completed';
            if (type === '0') url += '&type=stock';
            else if (type === '1') url += '&type=fixed';
            else url += '&type=all';
            if (date) url += `&date=${date}`;

            const res = await fetch(url);
            const tasks = await res.json();

            completedEmpty.classList.toggle('hidden', tasks.length !== 0);
            completedList.innerHTML = '';
            tasks.forEach(task => {
                const li = document.createElement('li');
                const span = document.createElement('span');
                span.className = 'task-content';
                span.textContent = task.content;
                li.appendChild(span);
                if (task.is_fixed && task.scheduled_date) {
                    const dateSpan = document.createElement('span');
                    dateSpan.className = 'date-label';
                    const d = new Date(task.scheduled_date + 'T00:00:00');
                    dateSpan.textContent = `${d.getMonth() + 1}/${d.getDate()}`;
                    li.appendChild(dateSpan);
                }
                completedList.appendChild(li);
            });
        }

        async function deleteTask(id) {
            await fetch(`api/tasks.php?id=${id}`, { method: 'DELETE' });
            loadActiveTasks();
        }

        document.getElementById('add-task-btn').addEventListener('click', async () => {
            const content = document.getElementById('add-content-input').value.trim();
            if (!content) return;
            const isFixed = isFixedToggle.checked ? 1 : 0;
            const date = addDateInput.value;
            if (isFixed && !date) return;

            await fetch('api/tasks.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(
                    isFixed ? { content, is_fixed: 1, scheduled_date: date } : { content }
                ),
            });

            document.getElementById('add-content-input').value = '';
            if (isFixed) addDateInput.value = '';
            loadActiveTasks();
        });

        loadActiveTasks();
        loadCompleted();
    </script>
</body>

</html>