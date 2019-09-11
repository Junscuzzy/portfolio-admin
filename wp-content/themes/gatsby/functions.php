<?php
/**
 * Gatsby functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Gatsby
 */

// Sets up theme defaults and registers support for various WordPress features.
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
} );


// Add Custom Post Types & Taxonomies
add_action( 'init', function () {
    register_post_type( 'portfolio', array(
        'labels' => array(
            'name' => __( 'Projets' ),
            'singular_name' => __( 'Projet' )
        ),
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'post-formats' ),
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => true,
    ) );

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


// Hide Bluid-in "Post" Post-type
add_action( 'admin_menu', function () {
    remove_menu_page( 'edit.php' );
} );
add_action( 'wp_dashboard_setup', function (){
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
}, 999 );


// Auto add "alt" attribute to image on upload from this title
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
		'page_title' 	=> __('Theme General Settings'),
		'menu_title'	=> __('Theme Settings'),
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> true
	));
}


// Add real site name and remove default wordpress link
add_action( 'wp_before_admin_bar_render', function () {
    global $wp_admin_bar;

    // 1. Remove unused menu items
    //$wp_admin_bar->remove_menu('wp-logo');		    // Remove the WordPress logo
    $wp_admin_bar->remove_menu('site-name');			// Remove the site name menu
    $wp_admin_bar->remove_menu('archive');
    $wp_admin_bar->remove_menu('view');	
    $wp_admin_bar->remove_menu('updates');				// Remove the updates link
    $wp_admin_bar->remove_menu('comments');				// Remove the user details tab
    $wp_admin_bar->remove_menu('customize');			// Remove W3 total cache plugin link
    $wp_admin_bar->remove_menu('new-post');				// Remove the blog new post link

    // 2. Add new site link
    $wp_admin_bar->add_menu(array(
        'id' => 'site_url',
        'title' => __('Voir le site'),
        'href' => 'https://juliencaron.eu',
        'meta' => array(
            'target' => '_blank'
        )
    ));
}, 999 ); 
