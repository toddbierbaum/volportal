{{-- Applies the user's theme preference (from localStorage, OS fallback) --}}
{{-- before first paint so there is no flash of the wrong theme, and again --}}
{{-- after every Livewire SPA navigation (wire:navigate syncs <html> attrs --}}
{{-- and strips the .dark class otherwise). --}}
<script>
    (function () {
        function applyTheme() {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const shouldBeDark = stored === 'dark' || (!stored && prefersDark);
            document.documentElement.classList.toggle('dark', shouldBeDark);
        }
        applyTheme();
        document.addEventListener('livewire:navigated', applyTheme);
    })();
</script>
