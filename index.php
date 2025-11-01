<?php get_header(); ?>

<main class="main" role="main">

    <?php
    /**
     * The template for displaying the front page.
     * MODIFIED: Fetches categories dynamically instead of from ACF.
     */

    // --- 1. Fetch all valid Level 1 categories ---
    $uncategorized_term = get_term_by('slug', 'uncategorized', 'product_cat');
    $exclude_ids = $uncategorized_term ? [$uncategorized_term->term_id] : [];

    $level_1_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => 0, // Get only top-level categories
        'hide_empty' => true, // Only get categories with products
        'exclude'    => $exclude_ids,
    ]);

    // If no valid categories are found, don't display the section.
    if (empty($level_1_categories)) {
        return;
    }

    // --- 2. Determine Active Tabs from URL or Defaults ---
    $default_l1_slug = isset($_GET['l1_cat']) ? sanitize_text_field($_GET['l1_cat']) : null;
    $default_l2_slug = isset($_GET['l2_cat']) ? sanitize_text_field($_GET['l2_cat']) : null;

    $active_l1_term = null;
    $active_l2_term = null;

    // Find the active L1 term (from URL or first in the list)
    if ($default_l1_slug) {
        $term_from_url = get_term_by('slug', $default_l1_slug, 'product_cat');
        // Check if this term exists in our fetched list
        if ($term_from_url && in_array($term_from_url->term_id, wp_list_pluck($level_1_categories, 'term_id'))) {
            $active_l1_term = $term_from_url;
        }
    }
    if (!$active_l1_term) {
        $active_l1_term = $level_1_categories[0]; // Default to the first valid category
    }

    // Fetch children of the active L1 term
    $child_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => $active_l1_term->term_id,
        'hide_empty' => true,
    ]);

    // Find the active L2 term (from URL or first child in the list)
    if (!empty($child_categories)) {
        if ($default_l2_slug) {
            $term_from_url = get_term_by('slug', $default_l2_slug, 'product_cat');
            // Validate it's a child of the active L1 term
            if ($term_from_url && $term_from_url->parent == $active_l1_term->term_id) {
                $active_l2_term = $term_from_url;
            }
        }
        if (!$active_l2_term) {
            $active_l2_term = $child_categories[0]; // Default to the first valid child
        }
    }

    // Get the number of products to show from the only remaining ACF field
    $settings            = get_field('products_to_show', 'option');
    $products_to_show    = ! empty($settings[ 'products_to_show' ] ) ? $settings[ 'products_to_show' ] : 4;
    $term_icon_id        = get_term_meta($active_l1_term->term_id, 'thumbnail_id', true);
    $active_l1_icon_url  = wp_get_attachment_image_url($term_icon_id,'full');
    ?>
    <section class="websima-product-tabs">
        <div class="container">
            <!-- Section Header -->
            <header class="websima-product-tabs__header">
                <div class="websima-product-tabs__header-main">
                    <?php if ($active_l1_icon_url) : ?>
                        <img src="<?php echo esc_url($active_l1_icon_url); ?>" alt="<?php echo esc_attr($active_l1_term->name); ?> icon" class="section-icon">
                    <?php else: ?>
                        <img src="" alt="" class="section-icon" style="display: none;"> <!-- Placeholder for JS -->
                    <?php endif; ?>
                    <h2 class="section-title"><?php echo esc_html($active_l1_term->name); ?></h2>
                </div>
                <a href="<?php echo esc_url(get_term_link($active_l1_term)); ?>" class="btn btn--link btn--arrow-left websima-product-tabs__archive-link show-desktop">
                    <span class="archive-link-text"><?php echo esc_html__('فروشگاه', 'websima-shop') . ' ' . esc_html($active_l1_term->name); ?></span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.6582 7.57812C2.6582 7.30198 2.88206 7.07812 3.1582 7.07812H12.0003C12.2765 7.07812 12.5003 7.30198 12.5003 7.57812C12.5003 7.85427 12.2765 8.07812 12.0003 8.07812H3.1582C2.88206 8.07812 2.6582 7.85427 2.6582 7.57812Z" fill="#FF6901"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.80465 7.22457C2.99991 7.02931 3.31649 7.02931 3.51176 7.22457L7.30123 11.014C7.49649 11.2093 7.49649 11.5259 7.30123 11.7212C7.10597 11.9164 6.78939 11.9164 6.59412 11.7212L2.80465 7.93168C2.60939 7.73642 2.60939 7.41983 2.80465 7.22457Z" fill="#FF6901"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.30123 3.43551C7.49649 3.63077 7.49649 3.94735 7.30123 4.14262L3.51176 7.93209C3.31649 8.12735 2.99991 8.12735 2.80465 7.93209C2.60939 7.73683 2.60939 7.42025 2.80465 7.22498L6.59412 3.43551C6.78939 3.24025 7.10597 3.24025 7.30123 3.43551Z" fill="#FF6901"/>
                    </svg>
                </a>
            </header>

            <!-- Wrapper for Level 1 Panels -->
            <div class="websima-product-tabs__level-1-panels">
                <?php foreach ($level_1_categories as $l1_term) :
                    $is_active_l1 = ($l1_term->term_id == $active_l1_term->term_id);
                    ?>
                    <div id="panel-<?php echo esc_attr($l1_term->term_id); ?>" class="tab-panel <?php echo $is_active_l1 ? 'is-active' : ''; ?>" role="tabpanel" <?php echo !$is_active_l1 ? 'hidden' : ''; ?>>
                        <?php if ($is_active_l1) : // Only render the content for the initially active L1 tab ?>
                            <?php if (!empty($child_categories) && $active_l2_term) : ?>
                                <nav class="websima-product-tabs__nav websima-product-tabs__nav--level-2" aria-label="Book age categories">
                                    <ul class="tabs-list" role="tablist">
                                        <?php foreach ($child_categories as $child_cat) : ?>
                                            <li role="presentation">
                                                <button class="tab-button <?php echo ($child_cat->term_id == $active_l2_term->term_id) ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo ($child_cat->term_id == $active_l2_term->term_id) ? 'true' : 'false'; ?>" aria-controls="panel-content-<?php echo esc_attr($child_cat->term_id); ?>" data-term-id="<?php echo esc_attr($child_cat->term_id); ?>" data-level="2">
                                                    <?php echo esc_html($child_cat->name); ?>
                                                </button>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </nav>
                                <div class="websima-product-tabs__level-2-panels">
                                    <?php foreach ($child_categories as $child_cat) : ?>
                                        <div id="panel-content-<?php echo esc_attr($child_cat->term_id); ?>" class="tab-panel <?php echo ($child_cat->term_id == $active_l2_term->term_id) ? 'is-active' : ''; ?>" role="tabpanel" <?php echo ($child_cat->term_id != $active_l2_term->term_id) ? 'hidden' : ''; ?>>
                                            <?php if ($child_cat->term_id == $active_l2_term->term_id) {
                                                get_websima_shop_products($active_l2_term->term_id, $products_to_show);
                                            } ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p><?php esc_html_e('هیچ زیر دسته ی دارای محصولی یافت نشد.', 'websima-shop'); ?></p>
                            <?php endif; ?>
                        <?php endif; // End check for active L1 tab ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mobile-btn-container">
                <a href="<?php echo esc_url(get_term_link($active_l1_term)); ?>" class="btn btn--link btn--arrow-left websima-product-tabs__archive-link show-mobile">
                    <span class="archive-link-text"><?php echo esc_html__('فروشگاه', 'websima-shop') . ' ' . esc_html($active_l1_term->name); ?></span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.6582 7.57812C2.6582 7.30198 2.88206 7.07812 3.1582 7.07812H12.0003C12.2765 7.07812 12.5003 7.30198 12.5003 7.57812C12.5003 7.85427 12.2765 8.07812 12.0003 8.07812H3.1582C2.88206 8.07812 2.6582 7.85427 2.6582 7.57812Z" fill="#FF6901"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.80465 7.22457C2.99991 7.02931 3.31649 7.02931 3.51176 7.22457L7.30123 11.014C7.49649 11.2093 7.49649 11.5259 7.30123 11.7212C7.10597 11.9164 6.78939 11.9164 6.59412 11.7212L2.80465 7.93168C2.60939 7.73642 2.60939 7.41983 2.80465 7.22457Z" fill="#FF6901"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.30123 3.43551C7.49649 3.63077 7.49649 3.94735 7.30123 4.14262L3.51176 7.93209C3.31649 8.12735 2.99991 8.12735 2.80465 7.93209C2.60939 7.73683 2.60939 7.42025 2.80465 7.22498L6.59412 3.43551C6.78939 3.24025 7.10597 3.24025 7.30123 3.43551Z" fill="#FF6901"/>
                    </svg>
                </a>
            </div>

            <!-- Level 1 Category Tabs (Bottom Tabs) -->
            <div class="swiper tabs-slider">
                <nav class="websima-product-tabs__nav websima-product-tabs__nav--level-1 swiper-wrapper" aria-label="Book main categories">
                    <?php foreach ($level_1_categories as $l1_term) : ?>
                        <?php
                        // --- GET DATA FOR EACH TAB ---
                        $archive_link = get_term_link($l1_term);
                        $icon_id      = get_term_meta($active_l1_term->term_id, 'thumbnail_id', true);
                        $icon_url     = wp_get_attachment_image_url($icon_id,'full');
                        $level_2_cats = get_terms([
                            'taxonomy'   => 'product_cat',
                            'parent'     => $l1_term->term_id, // Get only top-level categories
                            'hide_empty' => true, // Only get categories with products
                            'number'    => 3,
                            'orderby'    => 'count',
                        ]);
                        if (!empty($level_2_cats)) {
                            // 3. Extract just the 'name' property from each object into a simple array
                            $cat_names = wp_list_pluck($level_2_cats, 'name');

                            // 4. Join the names with the specified separator
                            $joined_names = implode('، ', $cat_names);

                            // 5. Create the final string with the ellipsis
                            $subtitle_text = $joined_names . ' و ...';
                        } else {
                            // 6. Provide a fallback text if no sub-categories exist
                            $subtitle_text = esc_html__('بدون زیر موضوع', 'websima-shop');
                        }
                        ?>
                        <div class="swiper-slide">
                            <button class="tab-button <?php echo ($l1_term->term_id == $active_l1_term->term_id) ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo ($l1_term->term_id == $active_l1_term->term_id) ? 'true' : 'false'; ?>" aria-controls="panel-<?php echo esc_attr($l1_term->term_id); ?>" data-term-id="<?php echo esc_attr($l1_term->term_id); ?>" data-level="1" data-term-name="<?php echo esc_attr($l1_term->name); ?>" data-archive-link="<?php echo esc_url($archive_link); ?>" data-icon-url="<?php echo esc_url($icon_url); ?>">
                                <span class="tab-button__title"><?php echo esc_html($l1_term->name); ?></span>
                                <span class="tab-button__subtitle"><?php echo $subtitle_text; ?></span>
                                <span class="tab-button__icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.6582 7.57812C2.6582 7.30198 2.88206 7.07812 3.1582 7.07812H12.0003C12.2765 7.07812 12.5003 7.30198 12.5003 7.57812C12.5003 7.85427 12.2765 8.07812 12.0003 8.07812H3.1582C2.88206 8.07812 2.6582 7.85427 2.6582 7.57812Z" fill="#FF6901"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.80465 7.22457C2.99991 7.02931 3.31649 7.02931 3.51176 7.22457L7.30123 11.014C7.49649 11.2093 7.49649 11.5259 7.30123 11.7212C7.10597 11.9164 6.78939 11.9164 6.59412 11.7212L2.80465 7.93168C2.60939 7.73642 2.60939 7.41983 2.80465 7.22457Z" fill="#FF6901"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.30123 3.43551C7.49649 3.63077 7.49649 3.94735 7.30123 4.14262L3.51176 7.93209C3.31649 8.12735 2.99991 8.12735 2.80465 7.93209C2.60939 7.73683 2.60939 7.42025 2.80465 7.22498L6.59412 3.43551C6.78939 3.24025 7.10597 3.24025 7.30123 3.43551Z" fill="#FF6901"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </nav>
                <div class="swiper-pagination tabs-slider-pagination"></div>
            </div>
        </div>
    </section>

    <!-- END: Product Tabs Section -->
</main>

<?php get_footer(); ?>
