// ============================================
// Theme Manager (Dark/Light Mode)
// ============================================
class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        this.applyTheme();
        this.createToggleButton();
    }

    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.theme);
        localStorage.setItem('theme', this.theme);
    }

    toggle() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        this.applyTheme();
        this.updateButton();
    }

    createToggleButton() {
        const button = document.createElement('button');
        button.className = 'theme-toggle';
        button.innerHTML = this.theme === 'light' ? '🌙' : '☀️';
        button.title = 'تبديل الوضع';
        button.onclick = () => {
            this.toggle();
            button.innerHTML = this.theme === 'light' ? '🌙' : '☀️';
        };
        document.body.appendChild(button);
    }

    updateButton() {
        const button = document.querySelector('.theme-toggle');
        if (button) {
            button.innerHTML = this.theme === 'light' ? '🌙' : '☀️';
        }
    }
}

// Toast Notification System
class ToastManager {
    static show(message, type = 'info', duration = 4000) {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        toast.innerHTML = `
            <span style="font-size: 20px;">${icons[type] || 'ℹ️'}</span>
            <span>${message}</span>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-100%)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', () => {
    new ThemeManager();
    
    // Add fade-in animation to main content
    const main = document.querySelector('main, .main-content');
    if (main) main.classList.add('fade-in');
});

// Export to window
window.ThemeManager = ThemeManager;
window.ToastManager = ToastManager;