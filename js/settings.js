(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var gearBtn = document.getElementById('gear-btn');
        var panel = document.getElementById('settings-panel');
        var toggle = document.getElementById('dark-toggle');

        toggle.checked = localStorage.getItem('darkMode') === 'on';

        gearBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.classList.toggle('hidden');
        });

        document.addEventListener('click', function () {
            panel.classList.add('hidden');
        });

        panel.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        toggle.addEventListener('change', function () {
            if (toggle.checked) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('darkMode', 'on');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('darkMode', 'off');
            }
        });
    });
})();
