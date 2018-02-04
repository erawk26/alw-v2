<?php
/**
 * Service post type
 */

 // Define our post type names
$service_names = [
    'name' => 'service',
    'singular' => 'Service',
    'plural' => 'Services',
    'slug' => 'services'
];

// Define our options
$service_options = [
    'exclude_from_search' => false,
    'hierarchical'        => false,
    'menu_position'       => 21,
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
$service = new PostType($service_names, $service_options);

// Set the menu icon
$service->icon('dashicons-hammer');

// Set the title placeholder text
$service->placeholder('Enter service name');

// Hide admin columns
$service->columns()->hide(['date', 'wpseo-score', 'wpseo-score-readability']);
