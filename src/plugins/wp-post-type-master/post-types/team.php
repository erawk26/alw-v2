<?php
/**
 * Team post type
 */

 // Define our post type names
$team_names = [
    'name' => 'team',
    'singular' => 'Team Member',
    'plural' => 'Team',
    'slug' => 'team'
];

// Define our options
$team_options = [
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
    'supports'            => array('title', 'editor'),
];

// Create post type
$team = new PostType($team_names, $team_options);

// Set the menu icon
$team->icon('dashicons-groups');

// Set the title placeholder text
$team->placeholder('Enter team member name');

// Hide admin columns
$team->columns()->hide(['date', 'wpseo-score', 'wpseo-score-readability']);
