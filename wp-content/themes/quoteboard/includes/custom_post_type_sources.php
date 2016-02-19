<?php
add_action( 'init', 'register_cpt_sources' );

function register_cpt_sources() {

    $labels = array( 
        'name' => _x( 'Sources', 'source' ),
        'singular_name' => _x( 'Source', 'source' ),
        'add_new' => _x( 'Add New', 'source' ),
        'add_new_item' => _x( 'Add New Source', 'source' ),
        'edit_item' => _x( 'Edit Source', 'source' ),
        'new_item' => _x( 'New Source', 'source' ),
        'view_item' => _x( 'View Source', 'source' ),
        'search_items' => _x( 'Search Sources', 'source' ),
        'not_found' => _x( 'No Sources found', 'source' ),
        'not_found_in_trash' => _x( 'No Sources found in Trash', 'source' ),
        'parent_item_colon' => _x( 'Parent Source:', 'source' ),
        'menu_name' => _x( 'Sources', 'source' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Tracks the places quotes have been said (movies, etc)',
        'supports' => array( 'author', 'title' ),
        //'taxonomies' => array( 'post_tag' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon'             => 'dashicons-book-alt',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array( 'slug' => 'sources', 'with_front' => false ),    // lets us set the archive page to /sources/ but keep singular sources at /source/[source_id]
        'capability_type' => 'post'
    );

    register_post_type( 'source', $args );
}