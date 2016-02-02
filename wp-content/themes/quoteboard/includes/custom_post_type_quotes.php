<?php
add_action( 'init', 'register_cpt_quotes' );

function register_cpt_quotes() {

    $labels = array( 
        'name' => _x( 'Quotes', 'quote' ),
        'singular_name' => _x( 'Quote', 'quote' ),
        'add_new' => _x( 'Add New', 'quote' ),
        'add_new_item' => _x( 'Add New Quote', 'quote' ),
        'edit_item' => _x( 'Edit Quote', 'quote' ),
        'new_item' => _x( 'New Quote', 'quote' ),
        'view_item' => _x( 'View Quotes', 'quote' ),
        'search_items' => _x( 'Search Quotes', 'quote' ),
        'not_found' => _x( 'No quotes found', 'quote' ),
        'not_found_in_trash' => _x( 'No quotes found in Trash', 'quote' ),
        'parent_item_colon' => _x( 'Parent Quote:', 'quote' ),
        'menu_name' => _x( 'Quotes', 'quote' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Put put the "quote" in "Quoteboard"!',
        'supports' => array( 'author', 'title', 'editor', 'excerpt', 'comments', 'custom-fields' ),
        'taxonomies' => array( 'post_tag' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon'             => 'dashicons-editor-quote',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array( 'slug' => 'quotes', 'with_front' => false ),    // lets us set the archive page to /quotes/ but keep singular quotes at /quote/[quote_id]
        'capability_type' => 'post'
    );

    register_post_type( 'quote', $args );
}