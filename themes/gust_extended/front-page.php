<?php
/**
 * Site front page — QR Space homepage.
 *
 * @package Gust_Extended
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header('qrspace');
get_template_part('template-parts/qr-space/homepage');
get_footer('qrspace');
