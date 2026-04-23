{{-- Applies the user's theme preference (from localStorage, OS fallback) --}}
{{-- before first paint so there is no flash of the wrong theme. Used by every --}}
{{-- layout that should respect the admin's dark/light toggle. --}}
<script>
    (function () {
        const stored = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (stored === 'dark' || (!stored && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
