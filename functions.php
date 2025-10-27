<?php

/**
 * Custom functions / External files
 */

define( 'WEBSIMA_VERSION', '1.0.0' );
require_once 'includes/custom-functions.php';


/**
 * Add support for useful stuff
 */

if ( function_exists( 'add_theme_support' ) ) {

    // Localisation Support
    load_theme_textdomain( 'websima-shop', get_template_directory() . '/languages' );
}


/**
 * Hide admin bar
 */

 add_filter( 'show_admin_bar', '__return_false' );


/**
 * Remove junk
 */

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'start_post_rel_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');


/**
 * Remove comments feed
 *
 * @return void
 */

function websima_shop_post_comments_feed_link() {
    return;
}

add_filter('post_comments_feed_link', 'websima_shop_post_comments_feed_link');


/**
 * Enqueue scripts
 */

function websima_shop_enqueue_scripts() {
    // wp_enqueue_style( 'fonts', '//fonts.googleapis.com/css?family=Font+Family' );
    // wp_enqueue_style( 'icons', '//use.fontawesome.com/releases/v5.0.10/css/all.css' );
    wp_enqueue_style('websima-shop-style', get_stylesheet_uri(), array(), WEBSIMA_VERSION );

    wp_enqueue_script('jquery');
    wp_enqueue_style( 'websima-shop-custom-styles', get_stylesheet_directory_uri() . '/assets/styles/style.css?' . filemtime( get_stylesheet_directory() . '/assets/styles/style.css' ) );
    wp_enqueue_script( 'websima-shop-custom-scripts', get_stylesheet_directory_uri() . '/assets/scripts/scripts.js?' . filemtime( get_stylesheet_directory() . '/assets/scripts/scripts.js' ), ['jquery'], null, true );

    // Enqueue SwiperJS CSS
    wp_enqueue_style('swiper-css', get_stylesheet_directory_uri() . '/assets/styles/swiper-bundle.min.css', array(), '12.0.03');

    // Enqueue SwiperJS JS
    wp_enqueue_script('swiper-js', get_stylesheet_directory_uri() . '/assets/scripts/swiper-bundle.min.js', array(), '12.0.03', true);

}
add_action( 'wp_enqueue_scripts', 'websima_shop_enqueue_scripts' );


/**
 * Add async and defer attributes to enqueued scripts
 *
 * @param string $tag
 * @param string $handle
 * @param string $src
 * @return void
 */

function defer_scripts( $tag, $handle, $src ) {

	// The handles of the enqueued scripts we want to defer
	$defer_scripts = [
        'SCRIPT_ID'
    ];

    // Find scripts in array and defer
    if ( in_array( $handle, $defer_scripts ) ) {
        return '<script type="text/javascript" src="' . $src . '" defer="defer"></script>' . "\n";
    }
    
    return $tag;
} 

add_filter( 'script_loader_tag', 'defer_scripts', 10, 3 );


/**
 * Add custom scripts to head
 *
 * @return string
 */

function add_gtag_to_head() {

    // Check is staging environment
    if ( strpos( get_bloginfo( 'url' ), '.test' ) !== false ) return;

    // Google Analytics
    $tracking_code = 'UA-*********-1';
    
    ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $tracking_code; ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '<?php echo $tracking_code; ?>');
        </script>
    <?php
}

add_action( 'wp_head', 'add_gtag_to_head' );



/**
 * Remove unnecessary scripts
 *
 * @return void
 */

function deregister_scripts() {
    wp_deregister_script( 'wp-embed' );
}

add_action( 'wp_footer', 'deregister_scripts' );


/**
 * Remove unnecessary styles
 *
 * @return void
 */

function deregister_styles() {
    wp_dequeue_style( 'wp-block-library' );
}

add_action( 'wp_print_styles', 'deregister_styles', 100 );


/**
 * Register nav menus
 *
 * @return void
 */

function websima_shop_setup() {

    add_theme_support('title-tag');

    add_theme_support('post-thumbnails');

    add_theme_support('woocommerce');

    register_nav_menus([
        'header' => 'Header',
        'footer' => 'Footer',
    ]);

    add_image_size('product-card-thumbnail', 400, 450, true); // 400px width, 450px height, hard crop

}
add_action( 'after_setup_theme', 'websima_shop_setup', 0 );

if (function_exists('acf_add_options_page')) {

    acf_add_options_page(array(
        'page_title'    => 'تنظیمات عمومی قالب',
        'menu_title'    => 'تنظیمات قالب',
        'menu_slug'     => 'theme-general-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
}



/**
 * Nav menu args
 *
 * @param array $args
 * @return void
 */

function websima_shop_nav_menu_args( $args ) {
    $args['container'] = false;
    $args['container_class'] = false;
    $args['menu_id'] = false;
    $args['items_wrap'] = '<ul class="%2$s">%3$s</ul>';

    return $args;
}

add_filter('wp_nav_menu_args', 'websima_shop_nav_menu_args');


/**
 * Button Shortcode
 *
 * @param array $atts
 * @param string $content
 * @return void
 */

function websima_shop_button_shortcode( $atts, $content = null ) {
    $atts['class'] = isset($atts['class']) ? $atts['class'] : 'btn';
    $atts['target'] = isset($atts['target']) ? $atts['target'] : '_self';
    return '<a class="' . $atts['class'] . '" href="' . $atts['link'] . '" target="'. $atts['target'] . '">' . $content . '</a>';
}

add_shortcode('button', 'websima_shop_button_shortcode');


/**
 * TinyMCE
 *
 * @param array $buttons
 * @return void
 */

function websima_shop_mce_buttons_2( $buttons ) {
    array_unshift( $buttons, 'styleselect' );
    $buttons[] = 'hr';

    return $buttons;
}

add_filter('mce_buttons_2', 'websima_shop_mce_buttons_2');


/**
 * TinyMCE styling
 *
 * @param array $settings
 * @return void
 */

function websima_shop_tiny_mce_before_init( $settings ) {
    $style_formats = [
        [
            'title' => 'Text Sizes',
            'items' => [
                [
                    'title'    => '2XL',
                    'selector' => 'span, p',
                    'classes'  => 'text-2xl'
                ],
                [
                    'title'    => 'XL',
                    'selector' => 'span, p',
                    'classes'  => 'text-xl'
                ],
                [
                    'title'    => 'LG',
                    'selector' => 'span, p',
                    'classes'  => 'text-lg'
                ],
                [
                    'title'    => 'MD',
                    'selector' => 'span, p',
                    'classes'  => 'text-md'
                ],
                [
                    'title'    => 'SM',
                    'selector' => 'span, p',
                    'classes'  => 'text-sm'
                ],
                [
                    'title'    => 'XD',
                    'selector' => 'span, p',
                    'classes'  => 'text-xs'
                ],                
            ]
        ]
    ];

    $settings['style_formats'] = json_encode($style_formats);
    $settings['style_formats_merge'] = true;

    return $settings;
}

add_filter('tiny_mce_before_init', 'websima_shop_tiny_mce_before_init');


/**
 * Get post thumbnail url
 *
 * @param string $size
 * @param boolean $post_id
 * @param boolean $icon
 * @return void
 */

function get_post_thumbnail_url( $size = 'full', $post_id = false, $icon = false ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $thumb_url_array = wp_get_attachment_image_src(
        get_post_thumbnail_id( $post_id ), $size, $icon
    );
    return $thumb_url_array[0];
}


/**
 * Add Front Page edit link to admin Pages menu
 */

function front_page_on_pages_menu() {
    global $submenu;
    if ( get_option( 'page_on_front' ) ) {
        $submenu['edit.php?post_type=page'][501] = array( 
            __( 'Front Page', 'websima_shop' ),
            'manage_options', 
            get_edit_post_link( get_option( 'page_on_front' ) )
        ); 
    }
}

add_action( 'admin_menu' , 'front_page_on_pages_menu' );
