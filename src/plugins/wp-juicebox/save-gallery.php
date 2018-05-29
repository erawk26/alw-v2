<?php
$dirname = dirname(__file__);
$position = strrpos($dirname, 'wp-content');
$document_root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
$wp_path = $position !== false ? substr($dirname, 0, $position) : rtrim($document_root, '/') . '/';

define('WP_USE_THEMES', false);

include_once $wp_path . 'wp-load.php';

$jb_gallery_added_nonce = isset($_POST['jb_gallery_added_nonce']) ? $_POST['jb_gallery_added_nonce'] : '';
if (!wp_verify_nonce($jb_gallery_added_nonce, 'jb_gallery_added')) {
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

$options = get_option('juicebox_options', array());
$options['last_id'] = isset($options['last_id']) ? $options['last_id'] + 1 : 1;
update_option('juicebox_options', $options);
$gallery_path = $Juicebox->get_gallery_path();
$gallery_id = $options['last_id'];
$gallery_filename = $gallery_path . $gallery_id . '.xml';
$Juicebox->build_gallery($gallery_filename, $_POST);

echo $gallery_id;
?>
