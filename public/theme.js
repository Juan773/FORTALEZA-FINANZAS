(() => {
    const key = 'fortaleza-theme';
    const root = document.documentElement;
    const apply = (theme) => {
        root.dataset.theme = theme === 'light' ? 'light' : 'dark';
        root.style.colorScheme = root.dataset.theme;
    };
    try {
        apply(localStorage.getItem(key));
    } catch {
        apply('dark');
    }
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-theme-toggle]')) return;
        apply(root.dataset.theme === 'dark' ? 'light' : 'dark');
        try {
            localStorage.setItem(key, root.dataset.theme);
        } catch {
            // The theme remains usable when browser storage is unavailable.
        }
    });
    window.addEventListener('storage', (event) => {
        if (event.key === key || event.key === null) apply(event.newValue);
    });
})();
