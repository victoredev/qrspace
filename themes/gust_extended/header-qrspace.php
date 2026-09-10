<?php
/**
 * Header for the QR Space marketing template.
 *
 * @package Gust_Extended
 */

if (!defined('ABSPATH')) {
    exit;
}

$home_url = home_url('/');
$account_url = qrspace_account_url();
$account_label = is_user_logged_in() ? __('Dashboard', 'gust-extended') : __('Log In', 'gust-extended');
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class('qrspace-home'); ?>>
<?php wp_body_open(); ?>
<div class="qs-page">
    <header class="qs-header">
        <div class="qs-shell qs-header__inner">
            <a class="qs-logo" href="<?php echo esc_url($home_url); ?>">
                <span class="qs-logo__mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" width="28" height="28">
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

            <nav class="qs-nav" id="qs-nav" aria-label="<?php esc_attr_e('Primary', 'gust-extended'); ?>">
                <div class="qs-nav__item qs-nav__item--has-menu">
                    <a href="#features"><?php esc_html_e('Features', 'gust-extended'); ?> <span class="qs-caret"></span></a>
                    <div class="qs-dropdown">
                        <a href="#features"><?php esc_html_e('Dynamic QR Codes', 'gust-extended'); ?></a>
                        <a href="#features"><?php esc_html_e('Scan Analytics', 'gust-extended'); ?></a>
                        <a href="#profiles"><?php esc_html_e('QR Profiles', 'gust-extended'); ?></a>
                        <a href="#features"><?php esc_html_e('All features', 'gust-extended'); ?></a>
                    </div>
                </div>
                <a class="qs-nav__item" href="#create"><?php esc_html_e('QR Code Generator', 'gust-extended'); ?></a>
                <div class="qs-nav__item qs-nav__item--has-menu">
                    <a href="#solutions"><?php esc_html_e('Solutions', 'gust-extended'); ?> <span class="qs-caret"></span></a>
                    <div class="qs-dropdown">
                        <a href="#solutions"><?php esc_html_e('For Business', 'gust-extended'); ?></a>
                        <a href="#solutions"><?php esc_html_e('For Personal Use', 'gust-extended'); ?></a>
                    </div>
                </div>
                <a class="qs-nav__item" href="#trust"><?php esc_html_e('Pricing', 'gust-extended'); ?></a>
                <div class="qs-nav__item qs-nav__item--has-menu">
                    <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: $home_url); ?>"><?php esc_html_e('Resources', 'gust-extended'); ?> <span class="qs-caret"></span></a>
                    <div class="qs-dropdown">
                        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: $home_url . '#trust'); ?>"><?php esc_html_e('Help Center', 'gust-extended'); ?></a>
                        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: $home_url); ?>"><?php esc_html_e('Blog', 'gust-extended'); ?></a>
                    </div>
                </div>
            </nav>

            <div class="qs-header__actions">
                <a class="qs-link" href="<?php echo esc_url($account_url); ?>"><?php echo esc_html($account_label); ?></a>
                <a class="qs-btn qs-btn--primary qs-btn--sm" href="#create"><?php esc_html_e('Create Free QR Code', 'gust-extended'); ?></a>
                <button class="qs-menu-toggle" type="button" aria-expanded="false" aria-controls="qs-nav" data-qs-menu>
                    <span></span><span></span><span></span>
                    <span class="screen-reader-text"><?php esc_html_e('Menu', 'gust-extended'); ?></span>
                </button>
            </div>
        </div>
    </header>
