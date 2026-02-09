/**
 * Dark Mode Toggle
 * Panel Pracowniczy Firma KOT
 */

(function() {
    'use strict';
    
    const THEME_KEY = 'preferred-theme';
    const THEME_ATTR = 'data-theme';
    
    /**
     * Initialize dark mode
     */
    function init() {
        // Get stored theme or use system preference
        const storedTheme = getStoredTheme();
        const systemTheme = getSystemTheme();
        const theme = storedTheme || systemTheme;
        
        // Apply theme
        setTheme(theme);
        
        // Create toggle button if not exists
        createToggleButton();
        
        // Listen for system theme changes
        watchSystemTheme();
    }
    
    /**
     * Get stored theme from localStorage
     */
    function getStoredTheme() {
        try {
            return localStorage.getItem(THEME_KEY);
        } catch (e) {
            return null;
        }
    }
    
    /**
     * Store theme in localStorage
     */
    function storeTheme(theme) {
        try {
            localStorage.setItem(THEME_KEY, theme);
        } catch (e) {
            // localStorage not available
        }
    }
    
    /**
     * Get system theme preference
     */
    function getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }
    
    /**
     * Set theme
     */
    function setTheme(theme) {
        document.documentElement.setAttribute(THEME_ATTR, theme);
        storeTheme(theme);
        
        // Update toggle button icon
        updateToggleButton(theme);
    }
    
    /**
     * Toggle theme
     */
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute(THEME_ATTR);
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    }
    
    /**
     * Create toggle button
     */
    function createToggleButton() {
        // Check if button already exists
        if (document.querySelector('.theme-toggle')) {
            return;
        }
        
        // Find header actions container
        const headerActions = document.querySelector('.header-actions');
        if (!headerActions) {
            return;
        }
        
        // Create button
        const button = document.createElement('button');
        button.className = 'theme-toggle';
        button.setAttribute('aria-label', 'Przełącz tryb ciemny');
        button.setAttribute('title', 'Przełącz tryb ciemny');
        
        // Create icons
        const moonIcon = `
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        `;
        
        const sunIcon = `
            <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        `;
        
        button.innerHTML = moonIcon + sunIcon;
        
        // Add click handler
        button.addEventListener('click', toggleTheme);
        
        // Insert button
        headerActions.insertBefore(button, headerActions.firstChild);
    }
    
    /**
     * Update toggle button icon
     */
    function updateToggleButton(theme) {
        const button = document.querySelector('.theme-toggle');
        if (!button) return;
        
        const moonIcon = button.querySelector('.icon-moon');
        const sunIcon = button.querySelector('.icon-sun');
        
        if (theme === 'dark') {
            if (moonIcon) moonIcon.style.display = 'none';
            if (sunIcon) sunIcon.style.display = 'block';
        } else {
            if (moonIcon) moonIcon.style.display = 'block';
            if (sunIcon) sunIcon.style.display = 'none';
        }
    }
    
    /**
     * Watch for system theme changes
     */
    function watchSystemTheme() {
        if (!window.matchMedia) return;
        
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        // Modern browsers
        if (mediaQuery.addEventListener) {
            mediaQuery.addEventListener('change', function(e) {
                // Only update if user hasn't set a preference
                if (!getStoredTheme()) {
                    setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
        // Older browsers
        else if (mediaQuery.addListener) {
            mediaQuery.addListener(function(e) {
                if (!getStoredTheme()) {
                    setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }
    
    /**
     * Expose toggle function globally
     */
    window.toggleDarkMode = toggleTheme;
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
