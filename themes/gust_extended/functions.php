<?php


/*
add_action( 'init', 'block_fireeye_referer', 1 );
function block_fireeye_referer() {
    if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
        $host = parse_url( wp_unslash( $_SERVER['HTTP_REFERER'] ), PHP_URL_HOST );
        if ( strcasecmp( $host, 'protect2.fireeye.com' ) === 0 ) {
            wp_die( 'Access denied.', 'Forbidden', [ 'response' => 403 ] );
        }
    }
}
*/
add_action('init', 'block_fireeye_referers', 1);
function block_fireeye_referers()
{
    if (empty($_SERVER['HTTP_REFERER'])) {
        return;
    }

    // extract host
    $host = parse_url(wp_unslash($_SERVER['HTTP_REFERER']), PHP_URL_HOST);
    if (! $host) {
        return;
    }

    // list of hosts to block
    $blocked = [
        'protect2.fireeye.com',
        'fireeye.com',
    ];

    // compare case-insensitive
    foreach ($blocked as $b) {
        if (strcasecmp($host, $b) === 0) {
            wp_die('Access denied.', 'Forbidden', [ 'response' => 403 ]);
        }
    }
}



add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
add_action('gust_init', 'init_gust_child');
add_action('woocommerce_before_main_content', 'woocommerce_breadcrumbs', 20);

add_filter('woocommerce_product_loop_start', 'mod_woocommerce_product_loop_start'); // Make List of Products into Grid
add_filter('woocommerce_product_loop_end', 'mod_woocommerce_product_loop_end'); // Make List of Products into Grid

add_filter('nav_menu_link_attributes', 'mod_gust_nav_bar', 11, 1); // Change The Navbar
add_filter('page_menu_link_attributes', 'mod_gust_nav_bar', 11, 1); // Change The Navbar

function enqueue_parent_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

function init_gust_child($gust)
{
    remove_woo_product_header();
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}

function woocommerce_breadcrumbs()
{

    $crumbs = [
      ['text' => 'Home', 'link' => get_home_url()],
      ['text' => 'Shop', 'link' => get_permalink(wc_get_page_id('shop'))],
    ];

    if (is_product()) {
        $post_id = get_the_ID();
        $product = wc_get_product($post_id);
        $crumbs[] = ['text' => $product->get_name(), 'link' => ''];
    } elseif (is_product_category()) {
        $term = get_queried_object();
        $crumbs[] = ['text' => $term->name, 'link' => get_term_link($term)];
    } elseif (is_cart()) {
        $crumbs[] = ['text' => get_the_title(wc_get_page_id('cart')), 'link' => wc_get_cart_url()];
    } elseif (is_checkout()) {
        $crumbs[] = ['text' => get_the_title(wc_get_page_id('checkout')), 'link' => wc_get_checkout_url()];
    }

    // Convert each item in the crumbs array to a link element
    $crumb_links = array_map(function ($crumb) {
        if (empty($crumb['link'])) {
            return '<span>' . $crumb['text'] . '</span>';
        } else {
            return '<a href="' . esc_url($crumb['link']) . '" class="hover:text-primary">' . $crumb['text'] . '</a>';
        }
    }, $crumbs);

    echo '<nav class="woocommerce-breadcrumbs max-w-screen-lg px-4 mx-auto mb-4 space-x-2">';
    echo implode('<span class="separator">/</span>', $crumb_links);
    echo '</nav>';
}

function remove_woo_product_header()
{
    $filters = $GLOBALS['wp_filter']['woocommerce_before_single_product'];
    if (empty($filters)) {
        return;
    }

    foreach ($filters->callbacks as $priority => $filter) {
        foreach ($filter as $identifier => $function) {
            if (is_array($function) && is_a($function['function'][0], 'Gust_WooCommerce')) {
                remove_action('woocommerce_before_single_product', array($function['function'][0], 'product_page_title'), $priority);
            }
        }
    }
}

function mod_woocommerce_product_loop_start()
{
    // Get the number of columns
    $columns = wc_get_loop_prop('columns');
    return '<div class="products clear-both grid grid-cols-1 md:grid-cols-2 lg:grid-cols-' . $columns . ' gap-8 md:gap-12">';
}

function mod_woocommerce_product_loop_end()
{
    return '</div>';
}

function mod_gust_nav_bar($atts)
{
    $atts['class'] = 'block p-2 border-b-2 border-transparent hover:text-primary';
    return $atts;
}


function cj_tawk_integration()
{
    //if (is_user_logged_in()) {
    //    if (current_user_can('manage_options')) {
    ?>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
    setTimeout(function() {
        // Load your chat script here

        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/6890e05d6ec67e192b91f43c/1j1quorpn';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();

    }, 5000); // 5000ms = 5 seconds
</script>
<!--End of Tawk.to Script-->
<?php
   //     }
   // }
}
add_action('wp_footer', 'cj_tawk_integration');
