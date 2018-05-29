<fieldset>

	<legend class="jb-legend"><b>Gallery Options</b></legend>

	<div id="jb-gallery-options">

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-gallery-title">Gallery Title</label>
		</div>
		<div class="jb-column-2">
			<input id="jb-gallery-title" type="text" name="galleryTitle" value="<?php echo htmlspecialchars($custom_values['galleryTitle']); ?>" />
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-e-library">Image Source</label>
		</div>
		<div class="jb-column-3">
			<select id="jb-e-library" name="e_library">
				<option value="media" <?php selected($custom_values['e_library'] === 'media'); ?>>Media Library</option>
				<option value="flickr" <?php selected($custom_values['e_library'] === 'flickr'); ?>>Flickr</option>
				<option value="nextgen" <?php selected($custom_values['e_library'] === 'nextgen'); ?>>NextGEN Gallery</option>
				<option value="picasa" <?php selected($custom_values['e_library'] === 'picasa'); ?>>Picasa Web Album</option>
			</select>
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div id="jb-toggle-source-media" class="jb-toggle-source">
			<div class="jb-column-1">
				<label for="jb-e-featured-image">Include Featured Image</label>
			</div>
			<div class="jb-column-3">
				<input id="jb-e-featured-image" type="checkbox" name="e_featuredImage" value="true" <?php checked($custom_values['e_featuredImage'] === 'true' || $custom_values['e_featuredImage'] === ''); ?> />
			</div>

			<div class="jb-column-1">
				<label for="jb-e-media-order">Image Order</label>
			</div>
			<div class="jb-column-3">
				<select id="jb-e-media-order" name="e_mediaOrder">
					<option value="ascending" <?php selected($custom_values['e_mediaOrder'] === 'ascending' || $custom_values['e_mediaOrder'] === ''); ?>>Ascending</option>
					<option value="descending" <?php selected($custom_values['e_mediaOrder'] === 'descending'); ?>>Descending</option>
				</select>
			</div>

			<div class="jb-column-clear">&nbsp;</div>

			<div class="jb-column-4">
<?php
				global $wp_version;
				$media_text = version_compare($wp_version, '3.5', '>=') ? 'Add Media' : 'Upload/Insert';
?>
				<span>Use the <?php echo $media_text;?>&nbsp;&nbsp;<img src="<?php echo admin_url() . 'images/media-button.png'; ?>" width="15" height="15" alt="<?php echo $media_text;?>" />&nbsp;&nbsp;button to add images</span>
			</div>
		</div>

		<div id="jb-toggle-source-flickr" class="jb-toggle-source">
			<div class="jb-column-1">
				<label for="jb-flickr-user-name">Flickr Username</label>
			</div>
			<div class="jb-column-3">
				<input id="jb-flickr-user-name" type="text" name="flickrUserName" value="<?php echo $custom_values['flickrUserName']; ?>" />
			</div>

			<div class="jb-column-1">
				<label for="jb-flickr-tags">Flickr Tags</label>
			</div>
			<div class="jb-column-3">
				<input id="jb-flickr-tags" type="text" name="flickrTags" value="<?php echo $custom_values['flickrTags']; ?>" />
			</div>
		</div>

		<div id="jb-toggle-source-nextgen" class="jb-toggle-source">
			<div class="jb-column-1">
				<label for="jb-e-nextgen-gallery-id">NextGEN Gallery Id</label>
			</div>
			<div class="jb-column-3">
				<input id="jb-e-nextgen-gallery-id" type="text" name="e_nextgenGalleryId" value="<?php echo $custom_values['e_nextgenGalleryId']; ?>" />
			</div>
		</div>

		<div id="jb-toggle-source-picasa" class="jb-toggle-source">
			<div class="jb-column-1">
				<label for="jb-e-picasa-user-id">Picasa User Id</label>
			</div>
			<div class="jb-column-3">
				<input id="jb-e-picasa-user-id" type="text" name="e_picasaUserId" value="<?php echo $custom_values['e_picasaUserId']; ?>" />
			</div>

			<div class="jb-column-1">
				<label for="jb-e-picasa-album-name">Picasa Album Id/Name</label>
			</div>
			<div class="jb-column-3">
				<input id="jb-e-picasa-album-name" type="text" name="e_picasaAlbumName" value="<?php echo $custom_values['e_picasaAlbumName']; ?>" />
			</div>
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-e-gallery-width">Gallery Width</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-e-gallery-width" type="text" name="e_galleryWidth" value="<?php echo $custom_values['e_galleryWidth']; ?>" />
		</div>

		<div class="jb-column-1">
			<label for="jb-e-gallery-height">Gallery Height</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-e-gallery-height" type="text" name="e_galleryHeight" value="<?php echo $custom_values['e_galleryHeight']; ?>" />
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-e-background-color">Background Color</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-e-background-color" type="text" name="e_backgroundColor" value="<?php echo $custom_values['e_backgroundColor']; ?>" />
		</div>

		<div class="jb-column-1">
			<label for="jb-e-background-opacity">Background Opacity</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-e-background-opacity" type="text" name="e_backgroundOpacity" value="<?php echo $custom_values['e_backgroundOpacity']; ?>" />
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-e-text-color">Text Color</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-e-text-color" type="text" name="e_textColor" value="<?php echo $custom_values['e_textColor']; ?>" />
		</div>

		<div class="jb-column-1">
			<label for="jb-e-text-opacity">Text Opacity</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-e-text-opacity" type="text" name="e_textOpacity" value="<?php echo $custom_values['e_textOpacity']; ?>" />
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-e-thumb-color">Thumb Frame Color</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-e-thumb-color" type="text" name="e_thumbColor" value="<?php echo $custom_values['e_thumbColor']; ?>" />
		</div>

		<div class="jb-column-1">
			<label for="jb-e-thumb-opacity">Thumb Frame Opacity</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-e-thumb-opacity" type="text" name="e_thumbOpacity" value="<?php echo $custom_values['e_thumbOpacity']; ?>" />
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-show-open-button">Show Open Button</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-show-open-button" type="checkbox" name="showOpenButton" value="true" <?php checked($custom_values['showOpenButton'] === 'true'); ?> />
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-show-expand-button">Show Expand Button</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-show-expand-button" type="checkbox" name="showExpandButton" value="true" <?php checked($custom_values['showExpandButton'] === 'true'); ?> />
		</div>

		<div class="jb-column-1">
			<label for="jb-show-thumbs-button">Show Thumbs Button</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-show-thumbs-button" type="checkbox" name="showThumbsButton" value="true" <?php checked($custom_values['showThumbsButton'] === 'true'); ?> />
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-use-thumb-dots">Use Thumb Dots</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-use-thumb-dots" type="checkbox" name="useThumbDots" value="true" <?php checked($custom_values['useThumbDots'] === 'true'); ?> />
		</div>

		<div class="jb-column-1">
			<label for="jb-use-fullscreen-expand">Use Fullscreen Expand</label>
		</div>
		<div class="jb-column-3">
			<input id="jb-use-fullscreen-expand" type="checkbox" name="useFullscreenExpand" value="true" <?php checked($custom_values['useFullscreenExpand'] === 'true'); ?> />
		</div>

		<div class="jb-column-clear">&nbsp;</div>

		<div class="jb-column-1">
			<label for="jb-pro-options">Pro Options</label>
		</div>
		<div class="jb-column-2">
			<textarea id="jb-pro-options" name="proOptions" cols="50" rows="5"><?php echo $pro_options; ?></textarea>
		</div>

		<div class="jb-column-clear">&nbsp;</div>

	</div>

</fieldset>
