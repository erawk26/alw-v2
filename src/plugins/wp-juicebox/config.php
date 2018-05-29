<?php
$dirname = dirname(__file__);
$position = strrpos($dirname, 'wp-content');
$document_root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
$wp_path = $position !== false ? substr($dirname, 0, $position) : rtrim($document_root, '/') . '/';

define('WP_USE_THEMES', false);

include_once $wp_path . 'wp-load.php';
include_once $wp_path . 'wp-admin/includes/plugin.php';

header('Content-Type: application/xml');

$gallery_id = isset($_GET['gallery_id']) ? $_GET['gallery_id'] : 0;

$gallery_path = $Juicebox->get_gallery_path();
$gallery_filename = $gallery_path . $gallery_id . '.xml';

if (is_file($gallery_filename)) {

	$dom_doc = new DOMDocument('1.0', 'UTF-8');
	$dom_doc->formatOutput = true;

	$settings_tag = $dom_doc->createElement('juiceboxgallery');

	$reset_values = $Juicebox->get_reset_values();

	$custom_values = $Juicebox->get_custom_values($gallery_filename);

	foreach ($custom_values as $key=>$value) {
		if (!(strpos($key, 'e_') === 0 || $key === 'postID' || (array_key_exists($key, $reset_values) && $value === $reset_values[$key]))) {
			$settings_tag->setAttribute($key, $value);
		}
	}

	switch ($custom_values['e_library']) {
		case 'media':
			$post_id = $custom_values['postID'];
			$post_record = $post_id !== '0' && get_post($post_id);
			if ($post_record) {
				$attachments = $Juicebox->get_attachments_media($custom_values['e_featuredImage'], $post_id);
				if ($attachments) {
					$attachments = array_values(array_filter($attachments, array(&$Juicebox, 'filter_image_media')));
					$attachments = $Juicebox->order_attachments($attachments, $custom_values['e_mediaOrder']);
					foreach ($attachments as $attachment) {
						$attachment_id = $attachment->ID;
						$thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail');
						$image = wp_get_attachment_image_src($attachment_id, 'full');
						if ($thumbnail && $image) {
							$image_element = $dom_doc->createElement('image');
							$image_element->setAttribute('imageURL', $image[0]);
							$image_element->setAttribute('thumbURL', $thumbnail[0]);
							$image_element->setAttribute('linkURL', $image[0]);
							$image_element->setAttribute('linkTarget', '_blank');
							$title_element = $dom_doc->createElement('title');
							$image_title = $attachment->post_title;
							$image_title = $Juicebox->line_break($image_title);
							$image_title = $Juicebox->strip_control_characters($image_title);
							$title_text = $dom_doc->createCDATASection($image_title);
							$title_element->appendChild($title_text);
							$image_element->appendChild($title_element);
							$caption_element = $dom_doc->createElement('caption');
							$image_caption = $attachment->post_excerpt;
							$image_caption = $Juicebox->line_break($image_caption);
							$image_caption = $Juicebox->strip_control_characters($image_caption);
							$caption_text = $dom_doc->createCDATASection($image_caption);
							$caption_element->appendChild($caption_text);
							$image_element->appendChild($caption_element);
							$settings_tag->appendChild($image_element);
						}
					}
				}
			}
			break;
		case 'nextgen':
			if (is_plugin_active('nextgen-gallery/nggallery.php') && $Juicebox->validate_gallery_nextgen($custom_values['e_nextgenGalleryId'])) {
				$attachments = $Juicebox->get_attachments_nextgen($custom_values['e_nextgenGalleryId']);
				if ($attachments) {
					$base_url = site_url('/' . $attachments[0]->path . '/');
					foreach ($attachments as $attachment) {
						$image_basename = $attachment->filename;
						$image_url = $Juicebox->encode_url($base_url . $image_basename);
						$thumb_url = $Juicebox->encode_url($base_url . 'thumbs/thumbs_' . $image_basename);
						$image_element = $dom_doc->createElement('image');
						$image_element->setAttribute('imageURL', $image_url);
						$image_element->setAttribute('thumbURL', $thumb_url);
						$image_element->setAttribute('linkURL', $image_url);
						$image_element->setAttribute('linkTarget', '_blank');
						$title_element = $dom_doc->createElement('title');
						$image_title = $attachment->alttext;
						$image_title = $Juicebox->line_break($image_title);
						$image_title = $Juicebox->strip_control_characters($image_title);
						$title_text = $dom_doc->createCDATASection($image_title);
						$title_element->appendChild($title_text);
						$image_element->appendChild($title_element);
						$caption_element = $dom_doc->createElement('caption');
						$image_caption = $attachment->description;
						$image_caption = $Juicebox->line_break($image_caption);
						$image_caption = $Juicebox->strip_control_characters($image_caption);
						$caption_text = $dom_doc->createCDATASection($image_caption);
						$caption_element->appendChild($caption_text);
						$image_element->appendChild($caption_element);
						$settings_tag->appendChild($image_element);
					}
				}
			}
			break;
		case 'picasa':
			$attachments = $Juicebox->get_attachments_picasa($custom_values['e_picasaUserId'], $custom_values['e_picasaAlbumName']);
			if ($attachments) {
				foreach ($attachments as $attachment) {
					$media = $attachment->children('http://search.yahoo.com/mrss/');
					$media_group = $media->group;
					$image_url = (string)$media_group->content->attributes()->{'url'};
					$thumb_url = (string)$media_group->thumbnail[1]->attributes()->{'url'};
					$image_element = $dom_doc->createElement('image');
					$image_element->setAttribute('imageURL', $image_url);
					$image_element->setAttribute('thumbURL', $thumb_url);
					$image_element->setAttribute('linkURL', $image_url);
					$image_element->setAttribute('linkTarget', '_blank');
					$title_element = $dom_doc->createElement('title');
					$image_title = $attachment->title;
					$image_title = $Juicebox->line_break($image_title);
					$image_title = $Juicebox->strip_control_characters($image_title);
					$title_text = $dom_doc->createCDATASection($image_title);
					$title_element->appendChild($title_text);
					$image_element->appendChild($title_element);
					$caption_element = $dom_doc->createElement('caption');
					$image_caption = $attachment->summary;
					$image_caption = $Juicebox->line_break($image_caption);
					$image_caption = $Juicebox->strip_control_characters($image_caption);
					$caption_text = $dom_doc->createCDATASection($image_caption);
					$caption_element->appendChild($caption_text);
					$image_element->appendChild($caption_element);
					$settings_tag->appendChild($image_element);
				}
			}
			break;
		case 'flickr':
		default:
			break;
	}

	$dom_doc->appendChild($settings_tag);

	echo $dom_doc->saveXML();
}
?>
