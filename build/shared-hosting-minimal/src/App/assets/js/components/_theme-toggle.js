/**
 * Theme Toggle Functionality
 * Handles switching between light and dark themes
 */

document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    
    // Check for saved theme preference or default to 'light'
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    // Apply saved theme
    setTheme(savedTheme);
    
    // Update toggle state
    themeToggle.checked = savedTheme === 'dark';
    
    // Listen for toggle changes
    themeToggle.addEventListener('change', function() {
        const newTheme = this.checked ? 'dark' : 'light';
        setTheme(newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Optional: Show a brief notification
        showThemeChangeNotification(newTheme);
    });
    
    /**
     * Set the theme
     * @param {string} theme - 'light' or 'dark'
     */
    function setTheme(theme) {
        body.setAttribute('data-bs-theme', theme);
        
        // Update meta theme-color for mobile browsers
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', theme === 'dark' ? '#121212' : '#ffffff');
        }
        
        // Dispatch custom event for other components that might need to know about theme changes
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: theme } 
        }));
    }
    
    /**
     * Show a brief notification when theme changes
     * @param {string} theme - The new theme
     */
    function showThemeChangeNotification(theme) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'theme-notification';
        notification.innerHTML = `
            <div class="alert alert-info alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 250px;">
                <i class="fas fa-${theme === 'dark' ? 'moon' : 'sun'}"></i>
                Switched to ${theme} theme
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
    
    /**
     * Detect system theme preference
     */
    function detectSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }
    
    /**
     * Listen for system theme changes
     */
    if (window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', function(e) {
            // Only auto-switch if user hasn't manually set a preference
            if (!localStorage.getItem('theme')) {
                const systemTheme = e.matches ? 'dark' : 'light';
                setTheme(systemTheme);
                themeToggle.checked = systemTheme === 'dark';
            }
        });
    }
    
    /**
     * Keyboard shortcut for theme toggle (Ctrl/Cmd + Shift + T)
     */
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
            e.preventDefault();
            themeToggle.click();
        }
    });
});

/**
 * Theme utilities for other components
 */
window.ThemeUtils = {
    /**
     * Get current theme
     * @returns {string} Current theme ('light' or 'dark')
     */
    getCurrentTheme: function() {
        return document.body.getAttribute('data-bs-theme') || 'light';
    },
    
    /**
     * Check if current theme is dark
     * @returns {boolean}
     */
    isDarkTheme: function() {
        return this.getCurrentTheme() === 'dark';
    },
    
    /**
     * Toggle theme programmatically
     */
    toggleTheme: function() {
        const toggle = document.getElementById('themeToggle');
        if (toggle) {
            toggle.click();
        }
    }
};
