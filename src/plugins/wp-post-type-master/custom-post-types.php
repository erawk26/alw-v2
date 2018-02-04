<?php
/*
    Plugin Name: Custom Post Types
    Plugin URI:
    Description: Registers all the custom post types for this site.
    Version: 2.0

    Uses PostTypes PHP class for creating post types by Joe Grainger
    https://github.com/jjgrainger/PostTypes
*/

defined('ABSPATH') or die('Do not access this file directly.');

require plugin_dir_path(__FILE__) . 'inc/class-columns.php';
require plugin_dir_path(__FILE__) . 'inc/class-posttype.php';
require plugin_dir_path(__FILE__) . 'inc/class-taxonomy.php';
require plugin_dir_path(__FILE__) . 'inc/class-taxfilter.php';

/**
 * Plugin activation tasks
 */
function vt_cpt_activate() {
    // Flush rewrites
	flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'vt_cpt_activate');

// Include post type(s)

require plugin_dir_path(__FILE__) . 'post-types/service.php';
require plugin_dir_path(__FILE__) . 'post-types/portfolio.php';
require plugin_dir_path(__FILE__) . 'post-types/team.php';
