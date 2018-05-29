<?php
/**
 Plugin Name: WP-Juicebox
 Plugin URI: https://www.juicebox.net/support/wp-juicebox/
 Description: Create Juicebox galleries with WordPress
 Author: Juicebox
 Version: 1.5.1
 Author URI: https://www.juicebox.net/
 Text Domain: juicebox
 */

/**
 * Juicebox plugin class
 */
class Juicebox {

	public $version = '1.5.1';
	private $required_core = false;

	/**
	 * Initalize plugin by registering hooks
	 */
	function __construct() {

		add_action('admin_menu', array(&$this, 'add_menu'));
		add_action('admin_head', array(&$this, 'add_javascript'));
		add_action('admin_enqueue_scripts', array(&$this, 'add_scripts_admin'));

		if (!is_admin()) {
			add_action('the_posts', array(&$this, 'shortcode_check'));
		}

		if (is_admin()) {
			add_action('media_buttons', array(&$this, 'add_media_button'), 999);
		}

		add_action('save_post', array(&$this, 'save_post_data'));

		if ($this->is_pro()) {
			add_filter('upgrader_pre_install', array(&$this, 'backup_pro'));
			add_filter('upgrader_post_install', array(&$this, 'restore_pro'));
		}

		add_shortcode('juicebox', array(&$this, 'shortcode_handler'));
	}

	/**
	 * Is pro
	 *
	 * @return boolean success
	 */
	function is_pro() {
		$file = plugin_dir_path(__FILE__) . 'jbcore/juicebox.js';
		$contents = file_get_contents($file);
		return strpos($contents, 'Juicebox-Pro') !== false;
	}

	/**
	 * Add menu
	 *
	 * @return void
	 */
	function add_menu() {
		add_menu_page('WP-Juicebox', 'WP-Juicebox', 'edit_posts', 'jb-manage-galleries', array(&$this, 'manage_galleries_page'), plugins_url('img/icon_16.png', __FILE__));
		add_submenu_page('jb-manage-galleries', 'WP-Juicebox - Manage Galleries', 'Manage Galleries', 'edit_posts', 'jb-manage-galleries', array(&$this, 'manage_galleries_page'));
		add_submenu_page('jb-manage-galleries', 'WP-Juicebox - Help', 'Help', 'edit_posts', 'jb-help', array(&$this, 'help_page'));
	}

	/**
	 * Add JavaScript
	 *
	 * @return void
	 */
	function add_javascript() {
		$current_screen = get_current_screen();
		$post_type = !empty($current_screen) ? $current_screen->post_type : 'post';
?>
		<script type="text/javascript">
			// <![CDATA[
			var jbPostType = '<?php echo $post_type; ?>';

			(function() {

				if (typeof jQuery === 'undefined') {
					return;
				}

				jQuery(document).ready(function() {
					if (typeof JB === 'undefined' || typeof JB.Gallery === 'undefined') {
						return;
					}
					JB.Gallery.configUrl = "<?php echo wp_nonce_url(plugins_url('jb-config.php', __FILE__), 'jb_add_gallery', 'jb_add_gallery_nonce'); ?>";
					jQuery('.jb-media-button').click(function() {
						if (typeof JB === 'undefined' || typeof JB.Gallery === 'undefined' || typeof JB.Gallery.embed === 'undefined' || typeof JB.Gallery.embed.apply !== 'function') {
							return;
						}
						JB.Gallery.embed.apply(JB.Gallery);
					});
				});

			})();
			// ]]>
		</script>
<?php
	}

	/**
	 * Add scripts admin
	 *
	 * @param string hook
	 * @return
	 */
	function add_scripts_admin($hook) {

		$generate = $hook === 'post.php' || $hook === 'post-new.php';
		$edit = preg_match('/jb-manage-galleries/', $hook);
		$help = preg_match('/jb-help/', $hook);

		if ($generate || $edit) {
			wp_enqueue_script('jquery');
		}

		if ($generate) {
			wp_register_script('jb_script_admin_generate', plugins_url('js/generate.js', __FILE__), array('jquery', 'thickbox'), $this->version);
			wp_enqueue_script('jb_script_admin_generate');
			wp_enqueue_script('thickbox');
			wp_register_style('jb_style_admin_media', plugins_url('css/media.css', __FILE__), array(), $this->version);
			wp_enqueue_style('jb_style_admin_media');
			wp_enqueue_style('thickbox');
		}

		if ($edit) {
			wp_register_script('jb_script_admin_table', plugins_url('js/table.js', __FILE__), array('jquery'), $this->version);
			wp_register_script('jb_script_admin_edit', plugins_url('js/edit.js', __FILE__), array('jquery'), $this->version);
			wp_enqueue_script('jb_script_admin_table');
			wp_enqueue_script('jb_script_admin_edit');
			wp_register_style('jb_style_admin_edit', plugins_url('css/edit.css', __FILE__), array(), $this->version);
			wp_enqueue_style('jb_style_admin_edit');
		}

		if ($help) {
			wp_register_style('jb_style_admin_help', plugins_url('css/help.css', __FILE__), array(), $this->version);
			wp_enqueue_style('jb_style_admin_help');
		}
	}

	/**
	 * Shortcode check
	 *
	 * @param array posts
	 * @return array posts
	 */
	function shortcode_check($posts) {
		if (!empty($posts) && !is_search() && !$this->required_core) {
			foreach ($posts as $post) {
				if (preg_match('/\[juicebox.*?gallery_id="[1-9][0-9]*".*?\]/i', $post->post_content)) {
					$this->required_core = true;
					add_action('wp_enqueue_scripts', array(&$this, 'add_scripts_wp_core'));
					break;
				}
			}
		}
		return $posts;
	}

	/**
	 * Add scripts wp core
	 *
	 * @return void
	 */
	function add_scripts_wp_core() {
		wp_register_script('jb_script_wp_core', plugins_url('jbcore/juicebox.js', __FILE__), array(), $this->version);
		wp_enqueue_script('jb_script_wp_core');
	}

	/**
	 * Add media button
	 *
	 * @return
	 */
	function add_media_button() {
		$current_screen = get_current_screen();
		$post_type = !empty($current_screen) ? $current_screen->post_type : 'post';

		if ($post_type === 'attachment' || ($post_type === 'page' && !current_user_can('edit_pages')) || ($post_type === 'post' && !current_user_can('edit_posts'))) {
			return;
		}

		echo '<a class="button jb-media-button" href="#" title="Add a Juicebox Gallery to your ' . $post_type . '"><img src="' . plugins_url('img/icon_16.png', __FILE__) . '" width="16" height="16" alt="button" /> Add Juicebox Gallery</a>';
	}

	/**
	 * Save post data
	 *
	 * @param string post id
	 * @return
	 */
	function save_post_data($post_id) {

		if ((isset($_POST['post_type']) && $_POST['post_type'] === 'attachment') || !current_user_can('edit_post', $post_id) || ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id))) {
			return;
		}

		$jb_term_id = get_post_meta($post_id, '_jb_term_id', true);
		if ($jb_term_id === '') {
			update_post_meta($post_id, '_jb_term_id', 'update');
			return;
		}

		$post_content = get_post_field('post_content', $post_id);

		preg_match_all('/\[juicebox.*?gallery_id="([1-9][0-9]*)".*?\]/i', $post_content, $matches, PREG_SET_ORDER);

		if ($matches) {
			$gallery_path = $this->get_gallery_path();
			for ($i = 0; $i < count($matches); $i++) {
				$gallery_filename = $gallery_path . intval($matches[$i][1]) . '.xml';
				if (is_file($gallery_filename)) {
					$this->set_post_id($gallery_filename, $post_id);
				}
			}
		}
	}

	/**
	 * Backup pro jbcore folder
	 *
	 * @return void
	 */
	function backup_pro() {
		$from = plugin_dir_path(__FILE__) . 'jbcore';
		$to = $this->get_upload_dir() . 'jbcore_backup';
		$this->copy_directory($from, $to);
	}

	/**
	 * Restore pro jbcore folder
	 *
	 * @return void
	 */
	function restore_pro() {
		$to = plugin_dir_path(__FILE__) . 'jbcore';
		$from = $this->get_upload_dir() . 'jbcore_backup';
		$this->delete_directory($to);
		$this->copy_directory($from, $to);
		$this->delete_directory($from);
	}

	/**
	 * Shortcode handler
	 *
	 * @param array attributes
	 * @return string embed code
	 */
	function shortcode_handler($atts) {
		extract(shortcode_atts(array('gallery_id'=>'0'), $atts));

		$clean_gallery_id = intval($this->clean_natural($gallery_id));

		if ($clean_gallery_id > 0) {

			$gallery_path = $this->get_gallery_path();
			$gallery_filename = $gallery_path . $clean_gallery_id . '.xml';

			if (is_file($gallery_filename)) {

				$custom_values = $this->get_custom_values($gallery_filename);

				$gallery_width = $custom_values['e_galleryWidth'];

				$gallery_height = $custom_values['e_galleryHeight'];

				$background_color = $this->get_rgba($custom_values['e_backgroundColor'], $custom_values['e_backgroundOpacity']);

				$config_url = plugins_url('config.php?gallery_id=' . $clean_gallery_id, __FILE__);

				$string_builder = '<!--START JUICEBOX EMBED-->' . PHP_EOL;
				$string_builder .= '<script type="text/javascript">' . PHP_EOL;
				$string_builder .= '	var jb_' . $clean_gallery_id . ' = new juicebox({' . PHP_EOL;
				$string_builder .= '		backgroundColor: "' . $background_color . '",' . PHP_EOL;
				$string_builder .= '		configUrl: "' . $config_url . '",' . PHP_EOL;
				$string_builder .= '		containerId: "juicebox-container-' . $clean_gallery_id . '",' . PHP_EOL;
				$string_builder .= '		galleryHeight: "' . $gallery_height . '",' . PHP_EOL;
				$string_builder .= '		galleryWidth: "' . $gallery_width . '"' . PHP_EOL;
				$string_builder .= '	});' . PHP_EOL;
				$string_builder .= '</script>' . PHP_EOL;
				$string_builder .= '<div id="juicebox-container-' . $clean_gallery_id . '"></div>' . PHP_EOL;
				$string_builder .= '<!--END JUICEBOX EMBED-->' . PHP_EOL;

				return $string_builder;
			} else {
				return '<div><p>Juicebox Gallery Id ' . $clean_gallery_id . ' cannot be found.</p></div>' . PHP_EOL;
			}
		} else {
			return '<div><p>Juicebox Gallery Id cannot be found.</p></div>' . PHP_EOL;
		}
	}

	/**
	 * Help page
	 *
	 * @return void
	 */
	function help_page() {
?>
		<div id="jb-help-page" class="wrap">

			<h2><img src="<?php echo plugins_url('img/icon_32.png', __FILE__); ?>" width="32" height="32" alt="logo" />&nbsp;WP-Juicebox - Help</h2>

			<p>
				<a href="https://www.juicebox.net/support/wp-juicebox/">Get support and view WP-Juicebox documentation.</a>
			</p>

		</div>
<?php
	}

	/**
	 * Add footer links
	 *
	 * @return void
	 */
	function add_footer_links() {
		$plugin_data = get_plugin_data(__FILE__);
		printf('%1$s Plugin | Version %2$s | By %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
	}

	/**
	 * Get reset values
	 *
	 * @return array reset values
	 */
	function get_reset_values() {

		$reset_values = array();

		$reset_values['galleryTitle'] = '';
		$reset_values['useFlickr'] = 'false';
		$reset_values['flickrUserName'] = '';
		$reset_values['flickrTags'] = '';
		$reset_values['textColor'] = 'rgba(255,255,255,1)';
		$reset_values['thumbFrameColor'] = 'rgba(255,255,255,0.5)';
		$reset_values['showOpenButton'] = 'true';
		$reset_values['showExpandButton'] = 'true';
		$reset_values['showThumbsButton'] = 'true';
		$reset_values['useThumbDots'] = 'false';
		$reset_values['useFullscreenExpand'] = 'false';
		$reset_values['e_library'] = 'media';
		$reset_values['e_featuredImage'] = 'true';
		$reset_values['e_mediaOrder'] = 'ascending';
		$reset_values['e_nextgenGalleryId'] = '';
		$reset_values['e_picasaUserId'] = '';
		$reset_values['e_picasaAlbumName'] = '';
		$reset_values['e_galleryWidth'] = '100%';
		$reset_values['e_galleryHeight'] = '600px';
		$reset_values['e_backgroundColor'] = '222222';
		$reset_values['e_backgroundOpacity'] = '1';
		$reset_values['e_textColor'] = 'ffffff';
		$reset_values['e_textOpacity'] = '1';
		$reset_values['e_thumbColor'] = 'ffffff';
		$reset_values['e_thumbOpacity'] = '0.5';
		$reset_values['postID'] = '0';

		return $reset_values;
	}

	/**
	 * Get values
	 *
	 * @param string filename
	 * @return array values
	 */
	function get_values($filename) {

		$values = array();

		if (is_file($filename)) {

			$dom_doc = new DOMDocument('1.0', 'UTF-8');
			$dom_doc->load($filename);

			$settings_tags = $dom_doc->getElementsByTagName('juiceboxgallery');
			$settings_tag = $settings_tags->item(0);

			if (!is_null($settings_tag)) {
				foreach ($settings_tag->attributes as $attribute) {
					$name = $attribute->nodeName;
					$value = $attribute->nodeValue;
					$values[$name] = $value;
				}
			}
		}

		return $values;
	}

	/**
	 * Get default values
	 *
	 * @return array default values
	 */
	function get_default_values() {

		$reset_values = $this->get_reset_values();

		$default_filename = $this->get_default_filename();

		$default_values = is_file($default_filename) ? $this->get_values($default_filename) : array();

		return array_merge($reset_values, $default_values);
	}

	/**
	 * Get custom values
	 *
	 * @param string gallery filename
	 * @return array custom values
	 */
	function get_custom_values($gallery_filename) {

		$default_values = $this->get_default_values();

		$reset_values = $this->strip_options($default_values, true);

		$custom_values = is_file($gallery_filename) ? $this->get_values($gallery_filename) : array();

		return array_merge($reset_values, $custom_values);
	}

	/**
	 * Get keys
	 *
	 * @return array keys
	 */
	function get_keys() {
		return array('galleryTitle', 'useFlickr', 'flickrUserName', 'flickrTags', 'textColor', 'thumbFrameColor', 'showOpenButton', 'showExpandButton', 'showThumbsButton', 'useThumbDots', 'useFullscreenExpand', 'e_library', 'e_featuredImage', 'e_mediaOrder', 'e_nextgenGalleryId', 'e_picasaUserId', 'e_picasaAlbumName', 'e_galleryWidth', 'e_galleryHeight', 'e_backgroundColor', 'e_backgroundOpacity', 'e_textColor', 'e_textOpacity', 'e_thumbColor', 'e_thumbOpacity', 'postID');
	}

	/**
	 * Get pro options
	 *
	 * @param simplexmlelement custom values
	 * @return string pro options
	 */
	function get_pro_options($custom_values) {

		$pro_options = '';

		$keys = $this->get_keys();
		$keys_lower = array_map('strtolower', $keys);

		foreach ($custom_values as $key=>$value) {
			if (!in_array(strtolower($key), $keys_lower, true)) {
				$pro_options .= $key . '="' . $value . '"' . "\n";
			}
		}

		return $pro_options;
	}

	/**
	 * Strip options
	 *
	 * @param simplexmlelement custom values
	 * @param boolean type
	 * @return array options
	 */
	function strip_options($custom_values, $type) {

		$options = array();

		$keys = $this->get_keys();
		$keys_lower = array_map('strtolower', $keys);

		foreach ($custom_values as $key=>$value) {
			if (in_array(strtolower($key), $keys_lower, true) === $type) {
				$options[$key] = $value;
			}
		}

		return $options;
	}

	/**
	 * Get post id
	 *
	 * @param string gallery filename
	 * @return string post id
	 */
	function get_post_id($gallery_filename) {

		$post_id = '0';

		if (is_file($gallery_filename)) {

			$dom_doc = new DOMDocument('1.0', 'UTF-8');
			$dom_doc->load($gallery_filename);

			$settings_tags = $dom_doc->getElementsByTagName('juiceboxgallery');
			$settings_tag = $settings_tags->item(0);

			$post_id = !is_null($settings_tag) && $settings_tag->hasAttribute('postID') ? $settings_tag->getAttribute('postID') : '0';
		}

		return $post_id;
	}

	/**
	 * Get upload directory
	 *
	 * @return string upload directory
	 */
	function get_upload_dir() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/';
	}

	/**
	 * Get gallery path
	 *
	 * @return string gallery path
	 */
	function get_gallery_path() {
		return $this->get_upload_dir() . 'juicebox/';
	}

	/**
	 * Get default filename
	 *
	 * @return string default filename
	 */
	function get_default_filename() {
		$gallery_path = $this->get_gallery_path();
		return $gallery_path . 'default.xml';
	}

	/**
	 * Get all galleries
	 *
	 * @param string gallery path
	 * @return array galleries
	 */
	function get_all_galleries($gallery_path) {
		$array = @scandir($gallery_path);
		return $array ? array_filter($array, array(&$this, 'filter_gallery')) : array();
	}

	/**
	 * Sort galleries descending
	 *
	 * @param string gallery
	 * @param string gallery
	 * @return integer gallery
	 */
	function sort_galleries_descending($a, $b) {
		$a_intval = intval(pathinfo($a, PATHINFO_FILENAME));
		$b_intval = intval(pathinfo($b, PATHINFO_FILENAME));
		if ($a_intval === $b_intval) {
			return 0;
		}
		return $a_intval > $b_intval ? -1 : 1;
	}

	/**
	 * Filter element
	 *
	 * @param string value
	 * @return boolean success
	 */
	function filter_element($value) {
		return $value !== '.' && $value !== '..';
	}

	/**
	 * Filter gallery
	 *
	 * @param string value
	 * @return boolean success
	 */
	function filter_gallery($value) {
		return preg_match('/^[1-9][0-9]*\.xml$/i', $value);
	}

	/**
	 * Filter image media
	 *
	 * @param string attachment
	 * @return boolean success
	 */
	function filter_image_media($attachment) {
		$mime = array('image/gif', 'image/jpeg', 'image/png');
		return in_array($attachment->post_mime_type, $mime, true);
	}

	/**
	 * Get rgba from color and opacity
	 *
	 * @param string color
	 * @param string opacity
	 * @return string rgba
	 */
	function get_rgba($color, $opacity) {
		return 'rgba(' . hexdec(substr($color, 0, 2)) . ',' . hexdec(substr($color, 2, 2)) . ',' . hexdec(substr($color, 4, 2)) . ',' . $opacity . ')';
	}

	/**
	 * Clean integer
	 *
	 * @param string integer
	 * @return string clean integer
	 */
	function clean_integer($integer) {
		return strval(intval(filter_var($integer, FILTER_SANITIZE_NUMBER_INT)));
	}

	/**
	 * Clean natural
	 *
	 * @param string natural
	 * @return string clean natural
	 */
	function clean_natural($natural) {
		return strval(abs(intval($this->clean_integer($natural))));
	}

	/**
	 * Clean percentage
	 *
	 * @param string percentage
	 * @return string clean percentage
	 */
	function clean_percentage($percentage) {
		return strval(min(intval($this->clean_natural($percentage)), 100));
	}

	/**
	 * Clean dimension
	 *
	 * @param string dimension
	 * @return string clean dimension
	 */
	function clean_dimension($dimension) {
		$dimension_string = $this->clean_natural($dimension);
		return substr(trim($dimension), -1) === '%' ? $this->clean_percentage($dimension_string) . '%' : $dimension_string . 'px';
	}

	/**
	 * Clean color
	 *
	 * @param string color
	 * @return string clean color
	 */
	function clean_color($color) {
		$output = strtolower(str_replace('0x', '', ltrim(trim($color), '#')));
		$length = strlen($output);
		if ($length < 3) {
			$output = str_pad($output, 3, '0');
		} elseif ($length > 3 && $length < 6) {
			$output = str_pad($output, 6, '0');
		} elseif ($length > 6) {
			$output = substr($output, 0, 6);
		}
		$new_length = strlen($output);
		if ($new_length === 3) {
			$r = dechex(hexdec(substr($output, 0, 1)));
			$g = dechex(hexdec(substr($output, 1, 1)));
			$b = dechex(hexdec(substr($output, 2, 1)));
			$output = $r . $r . $g . $g . $b . $b;
		} elseif ($new_length === 6) {
			$r = str_pad(dechex(hexdec(substr($output, 0, 2))), 2, '0', STR_PAD_LEFT);
			$g = str_pad(dechex(hexdec(substr($output, 2, 2))), 2, '0', STR_PAD_LEFT);
			$b = str_pad(dechex(hexdec(substr($output, 4, 2))), 2, '0', STR_PAD_LEFT);
			$output = $r . $g . $b;
		}
		return $output;
	}

	/**
	 * Clean decimal
	 *
	 * @param string decimal
	 * @return string clean decimal
	 */
	function clean_decimal($decimal) {
		return strval(abs(floatval(filter_var($decimal, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))));
	}

	/**
	 * Clean float
	 *
	 * @param string float
	 * @return string clean float
	 */
	function clean_float($float) {
		return strval(min(floatval($this->clean_decimal($float)), 1));
	}

	/**
	 * Build gallery
	 *
	 * @param string gallery filename
	 * @param array custom values
	 * @return void
	 */
	function build_gallery($gallery_filename, $custom_values) {

		$dom_doc = new DOMDocument('1.0', 'UTF-8');
		$dom_doc->formatOutput = true;

		$settings_tag = $dom_doc->createElement('juiceboxgallery');

		$clean_values = array();
		$clean_values['galleryTitle'] = trim(strip_tags(stripslashes($custom_values['galleryTitle']), '<a><b><br><font><i><u>'));
		if ($custom_values['e_library'] === 'flickr') {
			$clean_values['useFlickr'] = 'true';
			$clean_values['flickrUserName'] = trim($custom_values['flickrUserName']);
			$clean_values['flickrTags'] = trim($custom_values['flickrTags']);
		} else {
			$clean_values['useFlickr'] = 'false';
			$clean_values['flickrUserName'] = '';
			$clean_values['flickrTags'] = '';
		}
		$clean_values['e_textColor'] = $this->clean_color($custom_values['e_textColor']);
		$clean_values['e_textOpacity'] = $this->clean_float($custom_values['e_textOpacity']);
		$clean_values['textColor'] = $this->get_rgba($clean_values['e_textColor'], $clean_values['e_textOpacity']);
		$clean_values['e_thumbColor'] = $this->clean_color($custom_values['e_thumbColor']);
		$clean_values['e_thumbOpacity'] = $this->clean_float($custom_values['e_thumbOpacity']);
		$clean_values['thumbFrameColor'] = $this->get_rgba($clean_values['e_thumbColor'], $clean_values['e_thumbOpacity']);
		$clean_values['showOpenButton'] = isset($custom_values['showOpenButton']) ? $custom_values['showOpenButton'] : 'false';
		$clean_values['showExpandButton'] = isset($custom_values['showExpandButton']) ? $custom_values['showExpandButton'] : 'false';
		$clean_values['showThumbsButton'] = isset($custom_values['showThumbsButton']) ? $custom_values['showThumbsButton'] : 'false';
		$clean_values['useThumbDots'] = isset($custom_values['useThumbDots']) ? $custom_values['useThumbDots'] : 'false';
		$clean_values['useFullscreenExpand'] = isset($custom_values['useFullscreenExpand']) ? $custom_values['useFullscreenExpand'] : 'false';
		$clean_values['e_library'] = $custom_values['e_library'];
		$clean_values['e_featuredImage'] = '';
		$clean_values['e_mediaOrder'] = '';
		if ($custom_values['e_library'] === 'media') {
			$clean_values['e_featuredImage'] = isset($custom_values['e_featuredImage']) ? $custom_values['e_featuredImage'] : 'false';
			$clean_values['e_mediaOrder'] = $custom_values['e_mediaOrder'];
		}
		$clean_values['e_nextgenGalleryId'] = '';
		if ($custom_values['e_library'] === 'nextgen') {
			$clean_values['e_nextgenGalleryId'] = trim($custom_values['e_nextgenGalleryId']);
		}
		$clean_values['e_picasaUserId'] = '';
		$clean_values['e_picasaAlbumName'] = '';
		if ($custom_values['e_library'] === 'picasa') {
			$clean_values['e_picasaUserId'] = trim($custom_values['e_picasaUserId']);
			$clean_values['e_picasaAlbumName'] = trim($custom_values['e_picasaAlbumName']);
		}
		$clean_values['e_galleryWidth'] = $this->clean_dimension($custom_values['e_galleryWidth']);
		$clean_values['e_galleryHeight'] = $this->clean_dimension($custom_values['e_galleryHeight']);
		$clean_values['e_backgroundColor'] = $this->clean_color($custom_values['e_backgroundColor']);
		$clean_values['e_backgroundOpacity'] = $this->clean_float($custom_values['e_backgroundOpacity']);

		$pro_options = explode("\n", $custom_values['proOptions']);
		$all_options = array();
		foreach ($pro_options as $pro_option) {
			$attrs = explode('=', trim($pro_option), 2);
			if (count($attrs) === 2) {
				$all_options[$this->remove_whitespace($attrs[0])] = preg_replace('/^([`\'"])(.*)\\1$/', '\\2', trim(stripslashes($attrs[1])));
			}
		}

		$accepted_options = $this->strip_options($all_options, false);

		$complete_options = array_merge($clean_values, $accepted_options);

		foreach ($complete_options as $key=>$value) {
			$settings_tag->setAttribute($key, $value);
		}

		$dom_doc->appendChild($settings_tag);

		return (boolean)$dom_doc->save($gallery_filename);
	}

	/**
	 * Set post id
	 *
	 * @param string gallery filename
	 * @param string post id
	 * @return void
	 */
	function set_post_id($gallery_filename, $post_id) {

		if (is_file($gallery_filename) && $post_id !== '0') {

			$dom_doc = new DOMDocument('1.0', 'UTF-8');
			$dom_doc->preserveWhiteSpace = false;
			$dom_doc->formatOutput = true;
			$dom_doc->load($gallery_filename);

			$settings_tags = $dom_doc->getElementsByTagName('juiceboxgallery');
			$settings_tag = $settings_tags->item(0);

			if (!is_null($settings_tag)) {
				$settings_tag->setAttribute('postID', $post_id);
			}

			return (boolean)$dom_doc->save($gallery_filename);
		}
	}

	/**
	 * Get term
	 *
	 * @param array actual
	 * @param string total
	 * @return string term
	 */
	function get_term($actual, $total) {
		$term = '';
		$number = count($actual);
		switch ($number) {
			case 0:
				$term = 'no galleries';
				break;
			case 1:
				$term = $number === $total ? 'all galleries' : '1 gallery (Gallery Id ' . $actual[0] . ')';
				break;
			default:
				$term = $number === $total ? 'all galleries' : $number . ' galleries (Gallery Ids ' . preg_replace('/(.*),/', '\\1 and', implode(', ', $actual)) . ')';
				break;
		}
		return $term;
	}

	/**
	 * Manage galleries page
	 *
	 * @return void
	 */
	function manage_galleries_page() {
?>
		<div id="jb-manage-galleries-page" class="wrap">

			<h2><img src="<?php echo plugins_url('img/icon_32.png', __FILE__); ?>" width="32" height="32" alt="logo" />&nbsp;WP-Juicebox - Manage Galleries</h2>
<?php
			if (isset($_GET['jb-action']) && $_GET['jb-action'] !== '') {
				switch ($_GET['jb-action']) {
					case 'edit-gallery':
						$jb_edit_gallery_nonce = isset($_GET['jb_edit_gallery_nonce']) ? $_GET['jb_edit_gallery_nonce'] : '';
						if (!wp_verify_nonce($jb_edit_gallery_nonce, 'jb_edit_gallery')) {
							echo '<div class="error"><p>Remote submission prohibited.</p></div>';
							$this->gallery_table();
							break;
						}
						$gallery_path = $this->get_gallery_path();
						$gallery_id = $_GET['jb-gallery-id'];
						$gallery_filename = $gallery_path . $gallery_id . '.xml';
						if (is_file($gallery_filename)) {
							$this->edit_gallery_form($gallery_id);
						} else {
							echo '<div class="error"><p>Gallery Id ' . $gallery_id . ' cannot be found.</p></div>';
						}
						break;
					case 'gallery-edited':
						$jb_gallery_edited_nonce = isset($_POST['jb_gallery_edited_nonce']) ? $_POST['jb_gallery_edited_nonce'] : '';
						if (!wp_verify_nonce($jb_gallery_edited_nonce, 'jb_gallery_edited')) {
							echo '<div class="error"><p>Remote submission prohibited.</p></div>';
							$this->gallery_table();
							break;
						}
						$gallery_path = $this->get_gallery_path();
						$gallery_id = $_POST['jb-gallery-id'];
						$gallery_filename = $gallery_path . $gallery_id . '.xml';
						if (is_file($gallery_filename)) {
							$post_id = $this->get_post_id($gallery_filename);
							if ($this->build_gallery($gallery_filename, $_POST) && $this->set_post_id($gallery_filename, $post_id)) {
								echo '<div class="updated"><p>Gallery Id ' . $gallery_id . ' successfully edited.</p></div>';
							} else {
								echo '<div class="error"><p>Gallery Id ' . $gallery_id . ' cannot be edited.</p></div>';
							}
						} else {
							echo '<div class="error"><p>Gallery Id ' . $gallery_id . ' cannot be found.</p></div>';
						}
						$this->gallery_table();
						break;
					case 'delete-gallery':
						$jb_delete_gallery_nonce = isset($_GET['jb_delete_gallery_nonce']) ? $_GET['jb_delete_gallery_nonce'] : '';
						if (!wp_verify_nonce($jb_delete_gallery_nonce, 'jb_delete_gallery')) {
							echo '<div class="error"><p>Remote submission prohibited.</p></div>';
							$this->gallery_table();
							break;
						}
						$gallery_path = $this->get_gallery_path();
						$gallery_id = $_GET['jb-gallery-id'];
						$gallery_filename = $gallery_path . $gallery_id . '.xml';
						if (is_file($gallery_filename)) {
							if (unlink($gallery_filename)) {
								echo '<div class="updated"><p>Gallery Id ' . $gallery_id . ' successfully deleted.</p></div>';
							} else {
								echo '<div class="error"><p>Gallery Id ' . $gallery_id . ' cannot be deleted.</p></div>';
							}
						} else {
							echo '<div class="error"><p>Gallery Id ' . $gallery_id . ' cannot be found.</p></div>';
						}
						$this->gallery_table();
						break;
					case 'set-defaults':
						$jb_set_defaults_nonce = isset($_GET['jb_set_defaults_nonce']) ? $_GET['jb_set_defaults_nonce'] : '';
						if (!wp_verify_nonce($jb_set_defaults_nonce, 'jb_set_defaults')) {
							echo '<div class="error"><p>Remote submission prohibited.</p></div>';
							$this->gallery_table();
							break;
						}
						$this->set_defaults_form();
						break;
					case 'defaults-set':
						$jb_defaults_set_nonce = isset($_POST['jb_defaults_set_nonce']) ? $_POST['jb_defaults_set_nonce'] : '';
						if (!wp_verify_nonce($jb_defaults_set_nonce, 'jb_defaults_set')) {
							echo '<div class="error"><p>Remote submission prohibited.</p></div>';
							$this->gallery_table();
							break;
						}
						$default_filename = $this->get_default_filename();
						if ($this->build_gallery($default_filename, $_POST)) {
							echo '<div class="updated"><p>Custom default values successfully set.</p></div>';
						} else {
							echo '<div class="error"><p>Custom default values cannot be set.</p></div>';
						}
						$this->gallery_table();
						break;
					case 'reset-defaults':
						$jb_reset_defaults_nonce = isset($_GET['jb_reset_defaults_nonce']) ? $_GET['jb_reset_defaults_nonce'] : '';
						if (!wp_verify_nonce($jb_reset_defaults_nonce, 'jb_reset_defaults')) {
							echo '<div class="error"><p>Remote submission prohibited.</p></div>';
							$this->gallery_table();
							break;
						}
						$default_filename = $this->get_default_filename();
						if (is_file($default_filename)) {
							if (unlink($default_filename)) {
								echo '<div class="updated"><p>Default values successfully reset.</p></div>';
							} else {
								echo '<div class="error"><p>Default values cannot be reset.</p></div>';
							}
						} else {
							echo '<div class="updated"><p>No custom default values to reset.</p></div>';
						}
						$this->gallery_table();
						break;
					case 'delete-all-data':
						$jb_delete_all_data_nonce = isset($_GET['jb_delete_all_data_nonce']) ? $_GET['jb_delete_all_data_nonce'] : '';
						if (!wp_verify_nonce($jb_delete_all_data_nonce, 'jb_delete_all_data')) {
							echo '<div class="error"><p>Remote submission prohibited.</p></div>';
							$this->gallery_table();
							break;
						}
						$class = 'updated';
						$gallery_path = $this->get_gallery_path();
						$galleries = $this->get_all_galleries($gallery_path);
						$galleries_text = 'No galleries to delete.';
						if (!empty($galleries)) {
							$actual = array();
							foreach ($galleries as $gallery) {
								$gallery_filename = $gallery_path . $gallery;
								if (is_file($gallery_filename)) {
									if (unlink($gallery_filename)) {
										$actual[] = pathinfo($gallery, PATHINFO_FILENAME);
									}
								}
							}
							$total = count($galleries);
							$term = $this->get_term($actual, $total);
							$formatted_term = ucfirst($term);
							$galleries_text = $formatted_term . ' successfully deleted.';
						}
						$default_filename = $this->get_default_filename();
						$default_text = 'No custom default values to delete.';
						if (is_file($default_filename)) {
							if (unlink($default_filename)) {
								$default_text = 'All custom default values successfully deleted.';
							} else {
								$default_text = 'All custom default values cannot be deleted.';
								$class = 'error';
							}
						}
						$options = get_option('juicebox_options', array());
						$options_text = 'No options to delete.';
						if (!empty($options)) {
							if (delete_option('juicebox_options')) {
								$options_text = 'All options successfully deleted.';
							} else {
								$options_text = 'All options cannot be deleted.';
								$class = 'error';
							}
						}
						echo '<div class="' . $class . '"><p>' . $galleries_text . ' ' . $default_text . ' ' . $options_text . '</p></div>';
						$this->gallery_table();
						break;
					default:
						$this->gallery_table();
						break;
				}
			} else {
				$this->gallery_table();
			}
?>
		</div>
<?php
		add_action('in_admin_footer', array(&$this, 'add_footer_links'));
	}

	/**
	 * Gallery table
	 *
	 * @return void
	 */
	function gallery_table() {
?>
		<div class="jb-table-buttons">
			<form action="<?php echo admin_url('admin.php'); ?>" method="get">
				<input class="button jb-table-set" title="Set custom default values of the gallery configuration options." type="submit" name="table-set-header" value="Set Defaults" />
				<input type="hidden" name="page" value="jb-manage-galleries" />
				<input type="hidden" name="jb-action" value="set-defaults" />
<?php
				wp_nonce_field('jb_set_defaults', 'jb_set_defaults_nonce', false);
?>
			</form>
			<form action="<?php echo admin_url('admin.php'); ?>" method="get">
				<input class="button jb-table-reset" title="Reset the default values of the gallery configuration options to original values." type="submit" name="table-reset-header" value="Reset Defaults" />
				<input type="hidden" name="page" value="jb-manage-galleries" />
				<input type="hidden" name="jb-action" value="reset-defaults" />
<?php
				wp_nonce_field('jb_reset_defaults', 'jb_reset_defaults_nonce', false);
?>
			</form>
			<form action="<?php echo admin_url('admin.php'); ?>" method="get">
				<input class="button jb-table-delete" title="Delete all galleries, custom default values and options." type="submit" name="table-delete-header" value="Delete All Data" />
				<input type="hidden" name="page" value="jb-manage-galleries" />
				<input type="hidden" name="jb-action" value="delete-all-data" />
<?php
				wp_nonce_field('jb_delete_all_data', 'jb_delete_all_data_nonce', false);
?>
			</form>
		</div>

		<br />

		<div id="jb-bulk">
			<table class="wp-list-table widefat striped posts">

				<thead>
					<tr>
						<th>Gallery Id</th>
						<th>Last Modified Date</th>
						<th>Page/Post Title</th>
						<th>Gallery Title</th>
						<th>View Page/Post</th>
						<th>Edit Gallery</th>
						<th>Delete Gallery</th>
					</tr>
				</thead>

				<tbody>
<?php
				$gallery_path = $this->get_gallery_path();
				$galleries = $this->get_all_galleries($gallery_path);
				if (!empty($galleries)) {
					usort($galleries, array(&$this, 'sort_galleries_descending'));
					foreach ($galleries as $gallery) {
						$gallery_id = pathinfo($gallery, PATHINFO_FILENAME);
						$gallery_filename = $gallery_path . $gallery;
						if (is_file($gallery_filename)) {
							$custom_values = $this->get_custom_values($gallery_filename);
							$post_record = $custom_values['postID'] !== '0' && get_post($custom_values['postID']);
							$gallery_title = htmlspecialchars($custom_values['galleryTitle']);
							$display_title = $gallery_title !== '' ? $gallery_title : '<i>Untitled</i>';
							$post_type = get_post_type($custom_values['postID']);
							$post_type_text = ucfirst(strtolower($post_type));
							$post_trashed = get_post_status($custom_values['postID']) === 'trash';
?>
							<tr>
								<td><?php echo $gallery_id; ?></td>
								<td><?php echo date('d F Y H:i:s', filemtime($gallery_filename)); ?></td>
								<td>
<?php
									if ($post_trashed) {
										echo '<i>' . $post_type_text . ' has been trashed.</i>';
									} elseif ($post_record) {
										$the_title = get_the_title($custom_values['postID']);
										$post_title = $the_title !== '' ? $the_title : '<i>Untitled</i>';
										echo $post_title;
									} else {
										echo '<i>Page/post does not exist.</i>';
									}
?>
								</td>
								<td><?php echo $display_title; ?></td>
								<td>
<?php
									if ($post_trashed) {
										echo '<i>' . $post_type_text . ' has been trashed.</i>';
									} elseif ($post_record) {
										$text = 'View ' . $post_type_text;
										echo '<a href="' . get_permalink($custom_values['postID']) . '" title="' . $text . '">' . $text . '</a>';
									} else {
										echo '<i>Page/post does not exist.</i>';
									}
?>
								</td>
								<td><?php echo '<a href="' . wp_nonce_url(admin_url('admin.php?page=jb-manage-galleries&amp;jb-action=edit-gallery&amp;jb-gallery-id=' . $gallery_id), 'jb_edit_gallery', 'jb_edit_gallery_nonce') . '" title="Edit Gallery">Edit Gallery</a>'; ?></td>
								<td><?php echo '<a class="jb-delete-gallery" href="' . wp_nonce_url(admin_url('admin.php?page=jb-manage-galleries&amp;jb-action=delete-gallery&amp;jb-gallery-id=' . $gallery_id), 'jb_delete_gallery', 'jb_delete_gallery_nonce') . '" title="Delete Gallery">Delete Gallery</a>'; ?></td>
							</tr>
<?php
						}
					}
				} else {
?>
					<tr>
						<td>No galleries found.</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
<?php
				}
?>
				</tbody>

				<tfoot>
					<tr>
						<th>Gallery Id</th>
						<th>Last Modified Date</th>
						<th>Page/Post Title</th>
						<th>Gallery Title</th>
						<th>View Page/Post</th>
						<th>Edit Gallery</th>
						<th>Delete Gallery</th>
					</tr>
				</tfoot>

			</table>
		</div>

		<br />

		<div class="jb-table-buttons">
			<form action="<?php echo admin_url('admin.php'); ?>" method="get">
				<input class="button jb-table-set" title="Set custom default values of the gallery configuration options." type="submit" name="table-set-footer" value="Set Defaults" />
				<input type="hidden" name="page" value="jb-manage-galleries" />
				<input type="hidden" name="jb-action" value="set-defaults" />
<?php
				wp_nonce_field('jb_set_defaults', 'jb_set_defaults_nonce', false);
?>
			</form>
			<form action="<?php echo admin_url('admin.php'); ?>" method="get">
				<input class="button jb-table-reset" title="Reset the default values of the gallery configuration options to original values." type="submit" name="table-reset-footer" value="Reset Defaults" />
				<input type="hidden" name="page" value="jb-manage-galleries" />
				<input type="hidden" name="jb-action" value="reset-defaults" />
<?php
				wp_nonce_field('jb_reset_defaults', 'jb_reset_defaults_nonce', false);
?>
			</form>
			<form action="<?php echo admin_url('admin.php'); ?>" method="get">
				<input class="button jb-table-delete" title="Delete all galleries, custom default values and options." type="submit" name="table-delete-footer" value="Delete All Data" />
				<input type="hidden" name="page" value="jb-manage-galleries" />
				<input type="hidden" name="jb-action" value="delete-all-data" />
<?php
				wp_nonce_field('jb_delete_all_data', 'jb_delete_all_data_nonce', false);
?>
			</form>
		</div>
<?php
	}

	/**
	 * Edit gallery form
	 *
	 * @return void
	 */
	function edit_gallery_form($gallery_id) {
		$gallery_path = $this->get_gallery_path();
		$gallery_filename = $gallery_path . $gallery_id . '.xml';
		if (is_file($gallery_filename)) {
			$custom_values = $this->get_custom_values($gallery_filename);
			$pro_options = $this->get_pro_options($custom_values);
?>
			<div id="jb-edit-gallery-container" class="wrap jb-custom-wrap">

				<h3>Edit Juicebox Gallery Id <?php echo $gallery_id; ?></h3>

				<form id="jb-edit-gallery-form" action="<?php echo admin_url('admin.php?page=jb-manage-galleries&amp;jb-action=gallery-edited'); ?>" method="post">

					<input type="hidden" name="jb-gallery-id" value="<?php echo $gallery_id; ?>" />
<?php
					include_once plugin_dir_path(__FILE__) . 'fieldset.php';
?>
					<div id="jb-edit-action">
						<input id="jb-edit-gallery" class="button jb-button" type="submit" name="edit-gallery" value="Save" />
						<input id="jb-edit-cancel" class="button jb-button" type="button" name="edit-cancel" value="Cancel" />
					</div>
<?php
					wp_nonce_field('jb_gallery_edited', 'jb_gallery_edited_nonce', false);
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
						jQuery('#jb-edit-cancel').click(function() {
							jQuery('#jb-edit-gallery, #jb-edit-cancel').prop('disabled', true);
							window.location.href = '<?php echo admin_url('admin.php?page=jb-manage-galleries'); ?>';
						});
					});

				})();
				// ]]>
			</script>
<?php
		}
	}

	/**
	 * Set default values form
	 *
	 * @return void
	 */
	function set_defaults_form() {
		$custom_values = $this->get_default_values();
		$pro_options = $this->get_pro_options($custom_values);
?>
		<div id="jb-set-defaults-container" class="wrap jb-custom-wrap">

			<h3>Set Default Values</h3>

			<form id="jb-set-defaults-form" action="<?php echo admin_url('admin.php?page=jb-manage-galleries&amp;jb-action=defaults-set'); ?>" method="post">
<?php
				include_once plugin_dir_path(__FILE__) . 'fieldset.php';
?>
				<div id="jb-set-action">
					<input id="jb-set-defaults" class="button jb-button" type="submit" name="set-defaults" value="Set" />
					<input id="jb-set-cancel" class="button jb-button" type="button" name="set-cancel" value="Cancel" />
				</div>
<?php
				wp_nonce_field('jb_defaults_set', 'jb_defaults_set_nonce', false);
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
					jQuery('#jb-e-library').prop('disabled', true);
					jQuery(':input', '.jb-toggle-source').prop('disabled', true);
					jQuery('#jb-set-cancel').click(function() {
						jQuery('#jb-set-defaults, #jb-set-cancel').prop('disabled', true);
						window.location.href = '<?php echo admin_url('admin.php?page=jb-manage-galleries'); ?>';
					});
				});

			})();
			// ]]>
		</script>
<?php
	}

	/**
	 * Remove whitespace
	 *
	 * @param string input
	 * @return string output
	 */
	function remove_whitespace($input) {
		return preg_replace('/\\s+/', '', $input);
	}

	/**
	 * Validate gallery NextGEN
	 *
	 * @param string id
	 * @return boolean success
	 */
	function validate_gallery_nextgen($id) {
		$success = false;
		global $wpdb;
		$galleries = $wpdb->get_results("SELECT gid FROM " . $wpdb->prefix . "ngg_gallery ORDER BY gid ASC", ARRAY_A);
		foreach ($galleries as $gallery) {
			if ($id === $gallery['gid']) {
				$success = true;
				break;
			}
		}
		return $success;
	}

	/**
	 * Get attachments media
	 *
	 * @param string featured image
	 * @param string post id
	 * @return array attachments
	 */
	function get_attachments_media($featured_image, $post_id) {
		$query = array('order'=>'ASC', 'orderby'=>'menu_order', 'post_mime_type'=>'image', 'post_parent'=>$post_id, 'post_status'=>'any', 'post_type'=>'attachment', 'posts_per_page'=>-1);
		if ($featured_image === 'false') {
			$query['exclude'] = get_post_thumbnail_id($post_id);
		}
		$attachments = get_posts($query);
		return $attachments;
	}

	/**
	 * Get attachments NextGEN
	 *
	 * @param string NextGEN gallery id
	 * @return array attachments
	 */
	function get_attachments_nextgen($nextgen_gallery_id) {
		$attachments = array();
		global $wpdb;
		$ngg_options = get_option('ngg_options', array());
		$query = "SELECT t.*, tt.* FROM $wpdb->nggallery AS t INNER JOIN $wpdb->nggpictures AS tt ON t.gid = tt.galleryid WHERE t.gid = '$nextgen_gallery_id' AND tt.exclude != 1";
		if (isset($ngg_options['galSort']) && isset($ngg_options['galSortDir'])) {
			$query .= " ORDER BY tt.$ngg_options[galSort] $ngg_options[galSortDir]";
		}
		$entries = $wpdb->get_results($query);
		if (!is_null($entries)) {
			$attachments = $entries;
		}
		return $attachments;
	}

	/**
	 * Get attachments Picasa
	 *
	 * @param string Picasa user id
	 * @param string Picasa album name
	 * @return array attachments
	 */
	function get_attachments_picasa($picasa_user_id, $picasa_album_name) {
		$attachments = array();
		$name = $this->remove_whitespace($picasa_album_name);
		$term = preg_match('/^[0-9]{19}$/', $name) ? 'albumid' : 'album';
		$picasa_feed = 'http://picasaweb.google.com/data/feed/api/user/' . $this->remove_whitespace($picasa_user_id) . '/' . $term . '/' . $name . '?kind=photo&amp;imgmax=1600';
		$entries = @simplexml_load_file($picasa_feed);
		if ($entries) {
			foreach ($entries->entry as $entry) {
				$attachments[] = $entry;
			}
		}
		return $attachments;
	}

	/**
	 * Order attachments
	 *
	 * @param array attachments
	 * @param string order
	 * @return array output
	 */
	function order_attachments($attachments, $order) {
		$output = array();
		$all = $attachments;
		switch ($order) {
			case 'descending':
				$output = array_reverse($all);
				break;
			case 'ascending':
			default:
				$output = $all;
				break;
		}
		return $output;
	}

	/**
	 * Encode url
	 *
	 * @param string url
	 * @return string encoded url
	 */
	function encode_url($url) {
		$parse_url = parse_url($url);
		$scheme = isset($parse_url['scheme']) ? $parse_url['scheme'] : '';
		$host = isset($parse_url['host']) ? $parse_url['host'] : '';
		$path = isset($parse_url['path']) ? $parse_url['path'] : '';
		$query = isset($parse_url['query']) ? $parse_url['query'] : '';
		$implode_path = array();
		$explode_path = explode('/', $path);
		foreach ($explode_path as $fragment_path) {
			if ($fragment_path !== '') {
				$implode_path[] = rawurlencode($fragment_path);
			}
		}
		$new_path = !empty($implode_path) ? implode('/', $implode_path) : '';
		$implode_query = array();
		$explode_query = explode('&', $query);
		foreach ($explode_query as $fragment_query) {
			if ($fragment_query !== '') {
				$explode_fragment_query = explode('=', $fragment_query, 2);
				if (count($explode_fragment_query) === 2) {
					$implode_query[] = $explode_fragment_query[0] . '=' . urlencode($explode_fragment_query[1]);
				}
			}
		}
		$new_query = !empty($implode_query) ? '?' . implode('&', $implode_query) : '';
		return $scheme . '://' . $host . '/' . $new_path . $new_query;
	}

	/**
	 * Line break
	 *
	 * @param string input
	 * @return string output
	 */
	function line_break($input) {
		return preg_replace('/\r\n|\r|\n/', '<br />', $input);
	}

	/**
	 * Strip control characters
	 *
	 * @param string input
	 * @return string output
	 */
	function strip_control_characters($input) {
		$output = @preg_replace('/\p{Cc}+/u', '', $input);
		return $output ? $output : $input;
	}

	/**
	 * Copy directory
	 *
	 * @param string source
	 * @param string destination
	 * @return boolean success
	 */
	function copy_directory($source, $destination) {
		if (is_link($source)) {
			return symlink(readlink($source), $destination);
		}
		if (is_file($source)) {
			return copy($source, $destination);
		}
		if (!is_dir($destination)) {
			mkdir($destination);
		}
		$array = @scandir($source);
		if ($array) {
			$files = array_filter($array, array(&$this, 'filter_element'));
			foreach ($files as $file) {
				$this->copy_directory($source . '/' . $file, $destination . '/' . $file);
			}
			return true;
		}
		return false;
	}

	/**
	 * Delete directory
	 *
	 * @param string directory
	 * @return boolean success
	 */
	function delete_directory($directory) {
		if (!file_exists($directory)) {
			return false;
		}
		if (is_file($directory)) {
			return unlink($directory);
		}
		$array = @scandir($directory);
		if ($array) {
			$files = array_filter($array, array(&$this, 'filter_element'));
			foreach ($files as $file) {
				$this->delete_directory($directory . '/' . $file);
			}
			return rmdir($directory);
		}
		return false;
	}
}

/**
 * Main
 *
 * @return void
 */
function Juicebox() {
	global $Juicebox;
	$Juicebox = new Juicebox();
}

add_action('init', 'Juicebox');

/**
 * Check dependency
 *
 * @return void
 */
function jb_check_dependency() {

	// Check PHP version
	if (version_compare(phpversion(), '5.2', '<')) {
		jb_display_error_message('<b>WP-Juicebox</b> requires PHP v5.2 or later.', E_USER_ERROR);
	}

	// Check if DOM extention is enabled
	if (!class_exists('DOMDocument')) {
		jb_display_error_message('<b>WP-Juicebox</b> requires the DOM extention to be enabled.', E_USER_ERROR);
	}

	// Check WordPress version
	global $wp_version;
	if (version_compare($wp_version, '2.8', '<')) {
		jb_display_error_message('<b>WP-Juicebox</b> requires WordPress v2.8 or later.', E_USER_ERROR);
	}

	// Find path to WordPress uploads directory
	$upload_dir = wp_upload_dir();
	$gallery_path = $upload_dir['basedir'] . '/juicebox/';

	clearstatcache();

	// Create uploads folder and assign full access permissions
	if (!file_exists($gallery_path)) {
		$old = umask(0);
		if (!@mkdir($gallery_path, 0755, true)) {
			jb_display_error_message('<b>WP-Juicebox</b> cannot create the <b>wp-content/uploads/juicebox</b> folder. Please do this manually and assign full access permissions (755) to it.', E_USER_ERROR);
		}
		@umask($old);
		if ($old !== umask()) {
			jb_display_error_message('<b>WP-Juicebox</b> cannot cannot change back the umask after creating the <b>wp-content/uploads/juicebox</b> folder.', E_USER_ERROR);
		}
	} else {
		if (strncasecmp(php_uname(), 'win', 3) !== 0 && substr(sprintf('%o', fileperms($gallery_path)), -4) !== 0755) {
			$old = umask(0);
			if (!@chmod($gallery_path, 0755)) {
				jb_display_error_message('<b>WP-Juicebox</b> cannot assign full access permissions (755) to the <b>wp-content/uploads/juicebox</b> folder. Please do this manually.', E_USER_ERROR);
			}
			@umask($old);
			if ($old !== umask()) {
				jb_display_error_message('<b>WP-Juicebox</b> cannot cannot change back the umask after assigning full access permissions (755) to the <b>wp-content/uploads/juicebox</b> folder.', E_USER_ERROR);
			}
		}
	}
}

/**
 * Display error message
 *
 * @param string error message
 * @param integer error type
 */
function jb_display_error_message($error_msg, $error_type) {
	if(isset($_GET['action']) && $_GET['action'] === 'error_scrape') {
		echo $error_msg;
		exit;
	} else {
		trigger_error($error_msg, $error_type);
	}
}

register_activation_hook(__FILE__, 'jb_check_dependency');

?>
