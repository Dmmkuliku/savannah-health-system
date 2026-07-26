import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        mode: localStorage.getItem('shs-theme') || 'light',
        init() {
            this.apply(this.mode);
        },
        toggle() {
            this.apply(this.mode === 'dark' ? 'light' : 'dark');
        },
        apply(mode) {
            this.mode = mode;
            localStorage.setItem('shs-theme', mode);
            document.documentElement.classList.toggle('dark', mode === 'dark');
            const meta = document.querySelector('meta[name="theme-color"]');
            if (meta) {
                meta.setAttribute('content', mode === 'dark' ? '#0b1724' : '#1c7d5c');
            }
        },
    });
});

Alpine.start();
