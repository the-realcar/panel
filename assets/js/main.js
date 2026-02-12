/**
 * Main JavaScript
 * Panel Pracowniczy Firma KOT
 */

(function() {
    'use strict';
    
    /**
     * Initialize app
     */
    document.addEventListener('DOMContentLoaded', function() {
        initMobileMenu();
        initConfirmDialogs();
        initAutoHideAlerts();
        initTooltips();
    });
    
    /**
     * Mobile menu toggle
     */
    function initMobileMenu() {
        const toggle = document.querySelector('.nav-toggle');
        const nav = document.querySelector('.nav-list');
        
        if (toggle && nav) {
            toggle.addEventListener('click', function() {
                nav.classList.toggle('active');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                    nav.classList.remove('active');
                }
            });
        }
    }
    
    /**
     * Confirm dialogs for dangerous actions
     */
    function initConfirmDialogs() {
        const confirmButtons = document.querySelectorAll('[data-confirm]');
        
        confirmButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Czy na pewno chcesz wykonać tę operację?';
                
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }
    
    /**
     * Auto-hide alerts after 5 seconds
     */
    function initAutoHideAlerts() {
        const alerts = document.querySelectorAll('.alert[data-auto-hide]');
        
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    }
    
    /**
     * Simple tooltips
     */
    function initTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        
        tooltips.forEach(function(element) {
            element.addEventListener('mouseenter', function() {
                const text = this.getAttribute('data-tooltip');
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = text;
                tooltip.style.cssText = `
                    position: absolute;
                    background: var(--text);
                    color: white;
                    padding: 0.5rem 0.75rem;
                    border-radius: var(--radius-sm);
                    font-size: 0.875rem;
                    white-space: nowrap;
                    z-index: 1000;
                    pointer-events: none;
                `;
                
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + window.scrollY + 'px';
                tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
                
                this._tooltip = tooltip;
            });
            
            element.addEventListener('mouseleave', function() {
                if (this._tooltip) {
                    this._tooltip.remove();
                    this._tooltip = null;
                }
            });
        });
    }
    
    /**
     * Form validation helper
     */
    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;
        
        const inputs = form.querySelectorAll('[required]');
        let valid = true;
        
        inputs.forEach(function(input) {
            if (!input.value.trim()) {
                input.classList.add('error');
                valid = false;
            } else {
                input.classList.remove('error');
            }
        });
        
        return valid;
    };
    
    /**
     * AJAX helper
     */
    window.ajax = function(url, options) {
        options = options || {};
        
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (options.data) {
            config.body = JSON.stringify(options.data);
        }
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (options.success) {
                    options.success(data);
                }
                return data;
            })
            .catch(error => {
                if (options.error) {
                    options.error(error);
                }
                console.error('AJAX error:', error);
                throw error;
            });
    };
    
    /**
     * Show loading spinner
     */
    window.showLoading = function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.classList.add('loading');
            element.disabled = true;
        }
    };
    
    /**
     * Hide loading spinner
     */
    window.hideLoading = function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.classList.remove('loading');
            element.disabled = false;
        }
    };
    
    /**
     * Format number with thousands separator
     */
    window.formatNumber = function(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    };
    
    /**
     * Debounce function
     */
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };
    
})();
