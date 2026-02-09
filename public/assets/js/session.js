/**
 * Session Management
 * Auto-logout after 30 minutes of inactivity
 * Panel Pracowniczy Firma KOT
 */

(function() {
    'use strict';
    
    // Session timeout in milliseconds (30 minutes)
    const SESSION_TIMEOUT = 30 * 60 * 1000;
    
    // Warning time before logout (5 minutes)
    const WARNING_TIME = 5 * 60 * 1000;
    
    let sessionTimer;
    let warningTimer;
    let warningShown = false;
    
    /**
     * Initialize session management
     */
    function init() {
        // Start timers on page load
        resetTimers();
        
        // Reset timers on user activity
        document.addEventListener('mousemove', resetTimers);
        document.addEventListener('keypress', resetTimers);
        document.addEventListener('click', resetTimers);
        document.addEventListener('scroll', resetTimers);
        document.addEventListener('touchstart', resetTimers);
        
        // Check session periodically
        setInterval(checkSession, 60000); // Every minute
    }
    
    /**
     * Reset session timers
     */
    function resetTimers() {
        clearTimeout(sessionTimer);
        clearTimeout(warningTimer);
        warningShown = false;
        
        // Hide warning if shown
        hideWarning();
        
        // Set warning timer (25 minutes)
        warningTimer = setTimeout(showWarning, SESSION_TIMEOUT - WARNING_TIME);
        
        // Set logout timer (30 minutes)
        sessionTimer = setTimeout(logout, SESSION_TIMEOUT);
        
        // Update last activity on server
        updateLastActivity();
    }
    
    /**
     * Show session timeout warning
     */
    function showWarning() {
        if (warningShown) return;
        
        warningShown = true;
        
        const warning = document.createElement('div');
        warning.id = 'session-warning';
        warning.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--warning);
            color: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 10000;
            max-width: 400px;
        `;
        
        warning.innerHTML = `
            <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem;">Sesja wkrótce wygaśnie</h4>
            <p style="margin: 0 0 1rem 0; font-size: 0.875rem;">
                Twoja sesja wygaśnie za 5 minut. Wykonaj jakąkolwiek akcję, aby przedłużyć sesję.
            </p>
            <button onclick="hideSessionWarning()" style="
                background: white;
                color: var(--warning);
                border: none;
                padding: 0.5rem 1rem;
                border-radius: var(--radius-sm);
                cursor: pointer;
                font-weight: 600;
            ">
                Rozumiem
            </button>
        `;
        
        document.body.appendChild(warning);
    }
    
    /**
     * Hide session warning
     */
    function hideWarning() {
        const warning = document.getElementById('session-warning');
        if (warning) {
            warning.remove();
        }
    }
    
    /**
     * Global function to hide warning
     */
    window.hideSessionWarning = function() {
        hideWarning();
        resetTimers();
    };
    
    /**
     * Logout user
     */
    function logout() {
        // Show logout message
        const message = document.createElement('div');
        message.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--surface);
            color: var(--text);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            z-index: 10001;
            text-align: center;
        `;
        
        message.innerHTML = `
            <h3 style="margin: 0 0 1rem 0;">Sesja wygasła</h3>
            <p style="margin: 0; color: var(--text-muted);">
                Zostałeś wylogowany z powodu braku aktywności.<br>
                Za chwilę zostaniesz przekierowany do strony logowania...
            </p>
        `;
        
        document.body.appendChild(message);
        
        // Redirect to logout page
        setTimeout(function() {
            window.location.href = '/public/logout.php';
        }, 2000);
    }
    
    /**
     * Check session validity
     */
    function checkSession() {
        fetch('/public/check-session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                logout();
            }
        })
        .catch(error => {
            console.error('Session check failed:', error);
        });
    }
    
    /**
     * Update last activity on server
     */
    function updateLastActivity() {
        // Debounced to avoid too many requests
        if (updateLastActivity.timeout) {
            clearTimeout(updateLastActivity.timeout);
        }
        
        updateLastActivity.timeout = setTimeout(function() {
            // Ping server to update session
            fetch('/public/ping.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(error => {
                // Silently fail
            });
        }, 5000); // Wait 5 seconds after last activity
    }
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
