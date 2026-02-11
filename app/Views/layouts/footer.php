<?php
?>
            </div>
        </main>

        <footer class="footer">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> Firma KOT. Wszystkie prawa zastrzezone.</p>
                <p style="margin-top: 0.5rem; font-size: 0.75rem;">
                    Panel Pracowniczy v<?php echo e(APP_VERSION); ?>
                </p>
            </div>
        </footer>
    </div>

    <script src="/public/assets/js/main.js"></script>
    <script src="/public/assets/js/dark-mode.js"></script>
    <?php if (isLoggedIn()): ?>
    <script src="/public/assets/js/session.js"></script>
    <?php endif; ?>
</body>
</html>
