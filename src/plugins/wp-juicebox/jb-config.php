<?php
header('Cache-Control: max-age=0, must-revalidate, no-cache, no-store, post-check=0, pre-check=0');
header('Expires: Thu, 1 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Pragma: no-cache');

$dirname = dirname(__file__);
$position = strrpos($dirname, 'wp-content');
$document_root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
$wp_path = $position !== false ? substr($dirname, 0, $position) : rtrim($document_root, '/') . '/';

define('WP_USE_THEMES', false);

include_once $wp_path . 'wp-load.php';
include_once $wp_path . 'wp-admin/includes/screen.php';

remove_all_actions('wp_enqueue_scripts');
remove_all_actions('wp_footer');
remove_all_actions('wp_head');
remove_all_actions('wp_print_scripts');
remove_all_actions('wp_print_styles');

$jb_add_gallery_nonce = isset($_GET['jb_add_gallery_nonce']) ? $_GET['jb_add_gallery_nonce'] : '';
if (!wp_verify_nonce($jb_add_gallery_nonce, 'jb_add_gallery')) {
?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
		<head>
			<title>WP-Juicebox</title>
		</head>
		<body>
			<div><p>Remote submission prohibited.</p></div>
		</body>
	</html>
<?php
	exit();
}

$title = 'Add Juicebox Gallery';

$dir = is_rtl() ? 'dir=rtl' : 'dir=ltr';

global $wp_version;
$load = version_compare($wp_version, '4.5', '>=') ? 'load%5B%5D=about,admin-bar,admin-menu,buttons,common,dashboard,dashicons,edit,forms,l10n,list-tables,media,media-views,nav-menu,revisions,s,site-icon,themes,widgets,wp-auth-check' : 'load=wp-admin';

$options = get_option('juicebox_options', array());
$gallery_id = isset($options['last_id']) ? $options['last_id'] + 1 : 1;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php echo get_option('blog_charset'); ?>" />
		<link rel="stylesheet" href="<?php echo admin_url('load-styles.php?c=0&amp;' . $dir . '&amp;' . $load); ?>" type="text/css" media="all" />
		<link rel="stylesheet" href="<?php echo plugins_url('css/generate.css', __FILE__); ?>?ver=<?php echo $Juicebox->version; ?>" type="text/css" media="all" />
		<script src="<?php echo includes_url('js/jquery/jquery.js'); ?>" type="text/javascript" charset="utf-8"></script>
		<script src="<?php echo plugins_url('js/generate.js', __FILE__); ?>?ver=<?php echo $Juicebox->version; ?>" type="text/javascript" charset="utf-8"></script>
		<script src="<?php echo plugins_url('js/edit.js', __FILE__); ?>?ver=<?php echo $Juicebox->version; ?>" type="text/javascript" charset="utf-8"></script>
		<title><?php echo esc_html($title); ?> &lsaquo; <?php bloginfo('name') ?> &#8212; WordPress</title>
	</head>
	<body class="no-js wp-admin wp-core-ui">
<?php
		$custom_values = $Juicebox->get_default_values();
		$pro_options = $Juicebox->get_pro_options($custom_values);
?>
		<div id="jb-add-gallery-container" class="wrap jb-custom-wrap">

			<h2><img src ="<?php echo plugins_url('img/icon_32.png', __FILE__); ?>" width="32" height="32" align="top" alt="logo" /><?php echo esc_html($title); ?> Id <?php echo $gallery_id; ?></h2>

			<form id="jb-add-gallery-form" action="" method="post">
<?php
				include_once plugin_dir_path(__FILE__) . 'fieldset.php';
?>
				<div id="jb-add-action">
					<input id="jb-add-gallery" class="button jb-button" type="button" name="add-gallery" value="Add Gallery" />
					<input id="jb-add-cancel" class="button jb-button" type="button" name="add-cancel" value="Cancel" />
				</div>
<?php
				wp_nonce_field('jb_gallery_added', 'jb_gallery_added_nonce', false);
?>
			</form>

		</div>

		<script type="text/javascript">
			// <![CDATA[
			(function() {

				if (typeof jQuery === 'undefined') {
					return;
				}

				jQuery(document).ready(function() {
					try {
						JB.Gallery.Generator.postUrl = "<?php echo plugins_url('save-gallery.php', __FILE__); ?>";
						JB.Gallery.Generator.initialize();
					} catch (e) {
						throw "JB is undefined.";
					}
				});

			})();
			// ]]>
		</script>

	</body>

</html>
