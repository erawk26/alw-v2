<?php
/**
 * Portfolio post type
 */

 // Define our post type names
$portfolio_names = [
    'name' => 'portfolio',
    'singular' => 'Portfolio',
    'plural' => 'Portfolio',
    'slug' => 'portfolio'
];

// Define our options
$portfolio_options = [
    'exclude_from_search' => false,
    'hierarchical'        => false,
    'menu_position'       => 20,
    'has_archive'         => true,
    'rewrite'             => array('with_front' => false),
    'show_in_admin_bar'   => true,
    'show_in_menu'        => true,
    'show_in_nav_menus'   => true,
    'show_in_rest'        => false,
    'show_ui'             => true,
    'supports'            => array('title', 'editor', 'thumbnail'),
];

// Create post type
$portfolio = new PostType($portfolio_names, $portfolio_options);

// Set the menu icon
$portfolio->icon('dashicons-format-gallery');

// Set the title placeholder text
$portfolio->placeholder('Enter portfolio name');

// Hide admin columns
$portfolio->columns()->hide(['date', 'wpseo-score', 'wpseo-score-readability']);
