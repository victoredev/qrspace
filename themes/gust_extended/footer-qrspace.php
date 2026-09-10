<?php
/**
 * Footer for the QR Space marketing template.
 *
 * @package Gust_Extended
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
    <footer class="qs-footer">
        <div class="qs-shell qs-footer__inner">
            <a class="qs-logo qs-logo--footer" href="<?php echo esc_url(home_url('/')); ?>">
                <span class="qs-logo__mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" width="22" height="22">
                        <rect width="32" height="32" rx="8" fill="#4C5FFF"/>
                        <rect x="6" y="6" width="8" height="8" rx="1.5" fill="#fff"/>
                        <rect x="18" y="6" width="8" height="8" rx="1.5" fill="#fff"/>
                        <rect x="6" y="18" width="8" height="8" rx="1.5" fill="#fff"/>
                        <rect x="18" y="18" width="3" height="3" fill="#fff"/>
                        <rect x="23" y="18" width="3" height="3" fill="#fff"/>
                        <rect x="18" y="23" width="8" height="3" fill="#fff"/>
                    </svg>
                </span>
                <span class="qs-logo__text">QRSPACE</span>
            </a>
            <p class="qs-footer__copy">&copy; <?php echo esc_html(gmdate('Y')); ?> QR Space. <?php esc_html_e('Create QR codes free in seconds.', 'gust-extended'); ?></p>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
