(function() {

	if (typeof jQuery === 'undefined') {
		return;
	}

	jQuery(document).ready(function() {

		jQuery('#jb-edit-gallery-form, #jb-set-defaults-form').submit(function() {
			jQuery('.jb-button', this).prop('disabled', true);
			jQuery(':input', this).not('.jb-button', this).prop('disabled', false);
		});

		jQuery('#jb-e-library').change(function() {
			switch (jQuery('#jb-e-library').val()) {
				case 'media':
					jQuery('#jb-toggle-source-media').show();
					jQuery('#jb-toggle-source-flickr, #jb-toggle-source-nextgen, #jb-toggle-source-picasa').hide();
					break;
				case 'flickr':
					jQuery('#jb-toggle-source-flickr').show();
					jQuery('#jb-toggle-source-media, #jb-toggle-source-nextgen, #jb-toggle-source-picasa').hide();
					break;
				case 'nextgen':
					jQuery('#jb-toggle-source-nextgen').show();
					jQuery('#jb-toggle-source-media, #jb-toggle-source-flickr, #jb-toggle-source-picasa').hide();
					break;
				case 'picasa':
					jQuery('#jb-toggle-source-picasa').show();
					jQuery('#jb-toggle-source-media, #jb-toggle-source-flickr, #jb-toggle-source-nextgen').hide();
					break;
				default:
					break;
			}
		});

		jQuery('#jb-e-library').triggerHandler('change');

	});

})();
