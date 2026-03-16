<?php
/**
 * Common Footer
 * Panel Pracowniczy Firma KOT
 */
?>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> Firma KOT. Wszystkie prawa zastrzeżone.</p>
                <p style="margin-top: 0.5rem; font-size: 0.75rem;">
                    Panel Pracowniczy v<?php echo e(APP_VERSION); ?>
                </p>
            </div>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/dark-mode.js"></script>
    <?php if (isLoggedIn()): ?>
    <script>
        window.APP_SESSION_TIMEOUT_SECONDS = <?php echo (int)getSessionTimeoutSeconds(); ?>;
    </script>
    <script src="/assets/js/session.js"></script>
    <?php endif; ?>
</body>
</html>
