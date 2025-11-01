<?php
/**
 * The template for displaying product content within loops.
 */

defined('ABSPATH') || exit;

global $product;

// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
    return;
}

// --- Data Preparation ---
$is_on_sale = $product->is_on_sale();
$sale_price = $product->get_sale_price();
$regular_price = $product->get_regular_price();
$discount_percentage = 0;
if ($is_on_sale && !empty($regular_price) && $regular_price > 0) {
    $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
}
$multi_volume = get_field('multi_volume', $product->get_id());
$currency = get_woocommerce_currency_symbol();

?>
<div class="swiper-slide">
    <article <?php wc_product_class('product-card', $product); ?>>

        <a href="<?php echo esc_url($product->get_permalink()); ?>" class="product-card__image-link">
            <?php echo woocommerce_get_product_thumbnail('product-card-thumbnail'); ?>
        </a>

        <div class="product-card__details">
            <h3 class="product-card__title"><a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html( convert_to_persian_digits($product->get_name())); ?></a></h3>
            <?php if ($multi_volume) : ?>
                <span class="product-card__meta"><?php echo convert_to_persian_digits(__( sprintf( 'مجموعه %d جلدی', esc_html($multi_volume)), 'websima-shop')); ?></span>
            <?php endif; ?>
        </div>

        <div class="product-card__footer">

            <div class="product-card__price">
                <?php if ($is_on_sale && $discount_percentage > 0) : ?>

                    <span class="price">
                        <del aria-hidden="true"><?php echo convert_to_persian_digits(number_format(floatval($regular_price))); ?></del>
                        <span class="sale-badge"><?php echo esc_html( convert_to_persian_digits($discount_percentage)); ?>٪</span>
                        <ins>
                            <?php echo convert_to_persian_digits(number_format(floatval($sale_price))); ?>
                            <span class="currency"><?php echo $currency ?></span>
                        </ins>
                    </span>
                <?php elseif ($regular_price) : ?>
                    <span class="price">
                        <ins>
                            <?php echo convert_to_persian_digits(number_format(floatval($regular_price))); ?>
                            <span class="currency"><?php echo $currency ?></span>
                        </ins>
                    </span>
                <?php else: ?>
                    <span class="price"><ins><?php echo $product->get_price_html(); ?></ins></span>
                <?php endif; ?>
            </div>

            <!-- ---------------------------------------------------------------- -->
            <!-- SECTION: CUSTOM ADD TO CART BUTTON                               -->
            <!-- This replaces woocommerce_template_loop_add_to_cart()            -->
            <!-- ---------------------------------------------------------------- -->
            <?php
            // Use sprintf to build the link safely and cleanly.
            echo sprintf(
                '<a href="%s" data-quantity="1" class="product-card__add-to-cart %s" %s aria-label="%s" rel="nofollow">%s</a>',
                esc_url($product->add_to_cart_url()), // 1. Dynamic URL for simple/variable products
                esc_attr(implode(' ', array_filter(array( // 2. Essential classes for AJAX functionality
                    'button',
                    'product_type_' . $product->get_type(),
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                    $product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : '',
                )))),
                wc_implode_html_attributes(array( // 3. Data attributes
                    'data-product_id'  => $product->get_id(),
                    'data-product_sku' => $product->get_sku(),
                )),
                esc_attr($product->add_to_cart_description()), // 4. Accessibility label
                // 5. The custom SVG icon
                '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.88798 17.2745C5.12904 17.2745 4.5138 17.8898 4.5138 18.6487C4.5138 19.4077 5.12904 20.0229 5.88798 20.0229C6.64692 20.0229 7.26216 19.4077 7.26216 18.6487C7.26216 17.8898 6.64692 17.2745 5.88798 17.2745ZM3.33594 18.6487C3.33594 17.2393 4.47853 16.0967 5.88798 16.0967C7.29743 16.0967 8.44002 17.2393 8.44002 18.6487C8.44002 20.0582 7.29743 21.2008 5.88798 21.2008C4.47853 21.2008 3.33594 20.0582 3.33594 18.6487Z" fill="#FF6901"/><path fill-rule="evenodd" clip-rule="evenodd" d="M16.6868 17.2745C15.9279 17.2745 15.3126 17.8898 15.3126 18.6487C15.3126 19.4077 15.9279 20.0229 16.6868 20.0229C17.4457 20.0229 18.061 19.4077 18.061 18.6487C18.061 17.8898 17.4457 17.2745 16.6868 17.2745ZM14.1348 18.6487C14.1348 17.2393 15.2774 16.0967 16.6868 16.0967C18.0963 16.0967 19.2389 17.2393 19.2389 18.6487C19.2389 20.0582 18.0963 21.2008 16.6868 21.2008C15.2774 21.2008 14.1348 20.0582 14.1348 18.6487Z" fill="#FF6901"/><path fill-rule="evenodd" clip-rule="evenodd" d="M3.33594 2.94538C3.33594 2.62012 3.59961 2.35645 3.92487 2.35645H5.88798C6.21324 2.35645 6.47691 2.62012 6.47691 2.94538V16.0982H16.6851C17.0103 16.0982 17.274 16.3619 17.274 16.6871C17.274 17.0124 17.0103 17.2761 16.6851 17.2761H5.88798C5.56272 17.2761 5.29905 17.0124 5.29905 16.6871V3.53431H3.92487C3.59961 3.53431 3.33594 3.27064 3.33594 2.94538Z" fill="#FF6901"/><path fill-rule="evenodd" clip-rule="evenodd" d="M5.3023 4.86633C5.32548 4.5419 5.60727 4.29768 5.9317 4.32086L19.6735 5.30241C19.8365 5.31405 19.9873 5.3929 20.0899 5.52008C20.1925 5.64727 20.2376 5.81137 20.2145 5.97314L19.233 12.844C19.1915 13.1342 18.943 13.3497 18.65 13.3497H5.88974C5.56448 13.3497 5.30081 13.086 5.30081 12.7607C5.30081 12.4355 5.56448 12.1718 5.88974 12.1718H18.1392L18.9591 6.43225L5.84778 5.49573C5.52335 5.47256 5.27913 5.19077 5.3023 4.86633Z" fill="#FF6901"/></svg>'
            );
            ?>
        </div>

    </article>
</div>