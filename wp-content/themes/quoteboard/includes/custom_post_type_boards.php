<?php
add_action( 'init', 'register_cpt_boards' );

function register_cpt_boards() {

    $labels = array( 
        'name'                  => _x( 'Boards', 'board' ),
        'singular_name'         => _x( 'Board', 'board' ),
        'add_new'               => _x( 'Add New', 'board' ),
        'add_new_item'          => _x( 'Add New Board', 'board' ),
        'edit_item'             => _x( 'Edit Board', 'board' ),
        'new_item'              => _x( 'New Board', 'board' ),
        'view_item'             => _x( 'View Boards', 'board' ),
        'search_items'          => _x( 'Search Boards', 'board' ),
        'not_found'             => _x( 'No boards found', 'board' ),
        'not_found_in_trash'    => _x( 'No boards found in Trash', 'board' ),
        'parent_item_colon'     => _x( 'Parent Board:', 'board' ),
        'menu_name'             => _x( 'Boards', 'board' ),
    );

    $args = array( 
        'labels'                => $labels,
        'hierarchical'          => false,
        'description'           => 'We put the "board" in "Quoteboard"!',
        'supports'              => array( 'author', 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields' ),
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-format-gallery',
        'show_in_nav_menus'     => true,
        'publicly_queryable'    => true,
        'exclude_from_search'   => false,
        'has_archive'           => true,
        'query_var'             => true,
        'can_export'            => true,
        'rewrite'               => true,
        'capability_type'       => 'post'
    );
	
	$tax_args = array(
		'hierarchical'    => true,
		'label'           => 'Categories',
		'singular_label'  => 'Category',
	);

    register_post_type( 'board', $args );
	register_taxonomy( 'board_cats', 'board', $tax_args );
    register_taxonomy_for_object_type( 'board_cats', 'board' );
}