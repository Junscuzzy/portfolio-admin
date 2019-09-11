<?php
/**
 * Gatsby functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Gatsby
 */

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
add_action( 'after_setup_theme', function () {

    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support( 'title-tag' );

    /*
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support( 'post-thumbnails' );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus( array(
        'menu-1' => esc_html__( 'Primary', 'gatsby' ),
    ) );

} );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
add_action( 'widgets_init', function () {
    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar', 'gatsby' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Add widgets here.', 'gatsby' ),
    ) );
} );

/**
 * Enqueue scripts and styles.
 */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'gatsby-style', get_stylesheet_uri() );

//    wp_enqueue_script(
//        'gatsby-navigation',
//        get_template_directory_uri() . '/js/navigation.js',
//        array(),
//        '20151215',
//        true
//    );
} );

/**
 * Add Custom Post Types & Taxonomies
 */
add_action( 'init', function () {

    // Post-type: Portfolio
    register_post_type( 'portfolio', array(
        'labels' => array(
            'name' => __( 'Projets' ),
            'singular_name' => __( 'Projet' )
        ),
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'post-formats' ),
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => true,
    ) );

    // Taxonomy: Tags
    register_taxonomy('project_tag',array('portfolio'), array(
        'hierarchical' => false,
        'labels' => array(
            'name' => __( 'Tags' ),
            'singular_name' => __( 'Tag' )
        ),
        'public' => true,
        'show_in_rest' => true,
        'show_admin_column' => true
    ));

    // Taxonomy: Categories
    register_taxonomy('project_cat',array('portfolio'), array(
        'hierarchical' => true,
        'labels' => array(
            'name' => __( 'Categories' ),
            'singular_name' => __( 'Category' )
        ),
        'public' => true,
        'show_in_rest' => true,
        'show_admin_column' => true
    ));

} );

/**
 * Hide Bluid-in "Post" Post-type
 */

// The Side Menu
add_action( 'admin_menu', function () {
    remove_menu_page( 'edit.php' );
} );

// The +New Post in Admin Bar
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
    $wp_admin_bar->remove_node( 'new-post' );
}, 999 );

// The Quick Draft Dashboard Widget
add_action( 'wp_dashboard_setup', function (){
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
}, 999 );

/**
 * Auto add "alt" attribute to image on upload from this title
 *
 * @link https://developer.wordpress.org/reference/hooks/add_attachment/
 * @param int $post_ID ID de l'image
 */
add_action( 'add_attachment', function ( $post_ID ) {
    if ( wp_attachment_is_image( $post_ID ) ) {
        $img_title = get_post( $post_ID )->post_title;
        $img_title = preg_replace( '%\s*[-_\s]+\s*%', ' ', $img_title );
        $img_title = ucwords( strtolower( $img_title ) );

        update_post_meta( $post_ID, '_wp_attachment_image_alt', $img_title );
    }
} );

// Add ACF options page
if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> true
	));
	
}