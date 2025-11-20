/**
 * Dark Mode Toggle Functionality
 */
class DarkModeManager {
    constructor() {
        this.init();
    }

    init() {
        // Set initial theme based on saved preference or system preference
        this.setInitialTheme();
        
        // Add event listener for theme toggle
        this.setupToggleListener();
        
        // Listen for system theme changes
        this.setupSystemThemeListener();
    }

    setInitialTheme() {
        const savedTheme = localStorage.getItem('mediaorganizer-theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const theme = savedTheme || systemTheme;
        
        this.setTheme(theme);
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('mediaorganizer-theme', theme);
        
        // Update toggle button state
        this.updateToggleButton(theme);
    }

    updateToggleButton(theme) {
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            const sunIcon = toggleBtn.querySelector('.sun-icon');
            const moonIcon = toggleBtn.querySelector('.moon-icon');
            
            if (theme === 'dark') {
                sunIcon?.classList.add('active');
                moonIcon?.classList.remove('active');
                toggleBtn.title = 'Switch to light mode';
            } else {
                moonIcon?.classList.add('active');
                sunIcon?.classList.remove('active');
                toggleBtn.title = 'Switch to dark mode';
            }
        }
    }

    setupToggleListener() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('#theme-toggle, #theme-toggle *')) {
                e.preventDefault();
                const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                this.setTheme(newTheme);
            }
        });
    }

    setupSystemThemeListener() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            // Only apply system theme if user hasn't explicitly set a preference
            const savedTheme = localStorage.getItem('mediaorganizer-theme');
            if (!savedTheme) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    showThemeNotification(theme) {
        const notification = document.createElement('div');
        notification.className = 'position-fixed top-0 end-0 p-3';
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-body d-flex align-items-center">
                    <i class="bi bi-${theme === 'dark' ? 'moon-stars' : 'sun'} me-2"></i>
                    Switched to ${theme} mode
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }
}

// Initialize dark mode when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DarkModeManager();
});
