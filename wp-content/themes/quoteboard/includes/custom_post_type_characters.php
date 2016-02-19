<?php
add_action( 'init', 'register_cpt_characters' );

function register_cpt_characters() {

    $labels = array( 
        'name'                  => _x( 'Characters', 'character' ),
        'singular_name'         => _x( 'Character', 'character' ),
        'add_new'               => _x( 'Add New', 'character' ),
        'add_new_item'          => _x( 'Add New Character', 'character' ),
        'edit_item'             => _x( 'Edit Character', 'character' ),
        'new_item'              => _x( 'New Character', 'character' ),
        'view_item'             => _x( 'View Character', 'character' ),
        'search_items'          => _x( 'Search Characters', 'character' ),
        'not_found'             => _x( 'No Characters found', 'character' ),
        'not_found_in_trash'    => _x( 'No Characters found in Trash', 'character' ),
        'parent_item_colon'     => _x( 'Parent Character:', 'character' ),
        'menu_name'             => _x( 'Characters', 'character' ),
    );

    $args = array( 
        'labels'                => $labels,
        'hierarchical'          => false,
        'description'           => 'Characters, as in a movie or book',
        'supports'              => array( 'author', 'title', 'editor' ),
        //'taxonomies'          => array( 'post_tag' ),
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-groups',
        'show_in_nav_menus'     => true,
        'publicly_queryable'    => true,
        'exclude_from_search'   => false,
        'has_archive'           => true,
        'query_var'             => true,
        'can_export'            => true,
        'rewrite'               => array( 'slug' => 'characters', 'with_front' => false ),    // lets us set the archive page to /characters/ but keep singular characters at /character/[character_id]
        'capability_type'       => 'post'
    );

    register_post_type( 'character', $args );
}