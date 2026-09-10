<?php
/**
 * Template Name: QR Space
 * Template Post Type: page
 *
 * Marketing homepage layout for QR Space.
 *
 * @package Gust_Extended
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header('qrspace');
get_template_part('template-parts/qr-space/homepage');
get_footer('qrspace');
