<script>
(function () {
    const toggle = document.getElementById('theme-toggle');
    if (!toggle) return;
    toggle.addEventListener('click', function () {
        const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        // Persist for one year (path=/, SameSite=Lax)
        document.cookie = 'theme=' + next + '; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.className = next === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        }
    });
})();
</script>
