<?php
/**
 * This is file for all of your custom functions for the project
 */

// In functions.php

/**
 * AJAX handler for fetching tab content.
 */
function websima_shop_fetch_tab_content() {
    // 1. Security Check
    check_ajax_referer('websima_ajax_nonce', 'nonce');

    // 2. Get data from AJAX request
    $level = isset($_POST['level']) ? sanitize_text_field($_POST['level']) : '';
    $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;

    if (!$term_id || !$level) {
        wp_send_json_error('Invalid parameters.', 400);
    }

    // Get the number of products to show from ACF options page
    $settings = get_field('products_to_show', 'option');
    $products_to_show = ! empty( $settings[ 'products_to_show' ] ) ? $settings[ 'products_to_show' ] : 4 ;

    ob_start(); // Start output buffering

    if ($level === '1') {
        // --- Request is for a Level 1 Tab ---
        // NEW LOGIC: Fetch children directly, pick the first one as default.

        $child_categories = get_terms([
            'taxonomy'   => 'product_cat',
            'parent'     => $term_id,
            'hide_empty' => true, // Only get categories with products
        ]);

        if (!empty($child_categories)) {
            // The first child category is the new active one by default.
            $active_child_term = $child_categories[0];
            ?>
            <nav class="websima-product-tabs__nav websima-product-tabs__nav--level-2" aria-label="Book age categories">
                <ul class="tabs-list" role="tablist">
                    <?php foreach ($child_categories as $child_cat) : ?>
                        <li role="presentation">
                            <button class="tab-button <?php echo ($child_cat->term_id == $active_child_term->term_id) ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo ($child_cat->term_id == $active_child_term->term_id) ? 'true' : 'false'; ?>" aria-controls="panel-content-<?php echo esc_attr($child_cat->term_id); ?>" data-term-id="<?php echo esc_attr($child_cat->term_id); ?>" data-level="2">
                                <?php echo esc_html($child_cat->name); ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="websima-product-tabs__level-2-panels">
                <?php foreach ($child_categories as $child_cat) : ?>
                    <div id="panel-content-<?php echo esc_attr($child_cat->term_id); ?>" class="tab-panel <?php echo ($child_cat->term_id == $active_child_term->term_id) ? 'is-active' : ''; ?>" role="tabpanel" <?php echo ($child_cat->term_id != $active_child_term->term_id) ? 'hidden' : ''; ?>>
                        <?php if ($child_cat->term_id == $active_child_term->term_id) :
                            // Load products only for the active tab
                            get_websima_shop_products($active_child_term->term_id, $products_to_show);
                        endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        } else {
            echo '<p>' . esc_html__('هیچ زیر دسته ی دارای محصولی یافت نشد', 'websima-shop') . '</p>';
        }

    } elseif ($level === '2') {
        // --- This part remains unchanged ---
        get_websima_shop_products($term_id, $products_to_show);
    }

    $html = ob_get_clean();
    wp_send_json_success($html);
}
add_action('wp_ajax_fetch_tab_content', 'websima_shop_fetch_tab_content');
add_action('wp_ajax_nopriv_fetch_tab_content', 'websima_shop_fetch_tab_content');


/**
 * Reusable function to get products HTML.
 *
 * @param int $term_id The category term ID.
 * @param int $count The number of products to display.
 */
function get_websima_shop_products($term_id, $count) {
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'tax_query'      => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ],
        ],
    ];
    $products_query = new WP_Query($args);

    if ($products_query->have_posts()) : ?>
        <div class="swiper product-slider">
            <div class="swiper-wrapper">
                <?php while ($products_query->have_posts()) : $products_query->the_post();
                    wc_get_template_part('content', 'product');
                endwhile; ?>
            </div>
            <div class="swiper-pagination product-slider-pagination"></div>
        </div>
    <?php else :
        echo '<p>' . esc_html__('No products found in this category.', 'websima-shop') . '</p>';
    endif;
    wp_reset_postdata();
}

/**
 * Allow SVG uploads for admins.
 */
function websima_shop_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'websima_shop_mime_types');

/**
 * Converts English digits within a string to their Persian counterparts.
 *
 * @param string $string The input string that may contain English digits.
 * @return string The converted string with Persian digits.
 */
function convert_to_persian_digits($string) {
    // Return the original string if it's empty or null.
    if (empty($string)) {
        return $string;
    }

    $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $persian_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    // Replace all occurrences of English digits with Persian ones in one go.
    return str_replace($english_digits, $persian_digits, $string);
}