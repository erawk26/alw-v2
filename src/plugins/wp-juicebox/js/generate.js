var jbGetEditor = function() {
	var win = window.parent || window;
	if (typeof win.CKEDITOR !== 'undefined') {
		return 'ckeditor';
	} else if (typeof win.CodeMirror !== 'undefined') {
		return 'codemirror';
	} else if (typeof win.FCKeditor !== 'undefined') {
		return 'fckeditor';
	} else if (typeof win.quickPressLoad !== 'undefined') {
		return 'quickpress';
	} else if (typeof win.tinyMCE !== 'undefined' && (!win.tinyMCE.activeEditor || win.tinyMCE.activeEditor.isHidden())) {
		return 'text';
	} else if (typeof win.tinyMCE !== 'undefined' && win.tinyMCE.activeEditor && !win.tinyMCE.activeEditor.isHidden()) {
		return 'visual';
	} else {
		return 'unknown';
	}
};

var jbGetId = function() {
	return jQuery('.wp-media-buttons:eq(0) .add_media').attr('data-editor') || 'content';
};

var JB = window.JB || {};

JB.Gallery = function() {

	return {

		embed: function() {

			if (typeof jQuery === 'undefined') {
				return;
			}

			var win = window.parent || window;

			var editor = jbGetEditor();
			var id = jbGetId();

			if (editor === 'ckeditor' && (typeof win.CKEDITOR.instances.content === 'undefined' || win.CKEDITOR.instances.content.mode !== 'wysiwyg')) {
				win.alert('You must be in WYSIWYG edit mode to insert a Juicebox Gallery shortcode tag.');
				return;
			}
			if (editor === 'codemirror') {
				win.alert('The CodeMirror editor is not supported by WP-Juicebox. Please copy and paste the Juicebox Gallery shortcode tag when instructed.');
			}
			if (editor === 'fckeditor' && win.FCKeditorAPI.GetInstance(id).EditMode !== 0) {
				win.alert('You must be in WYSIWYG edit mode to insert a Juicebox Gallery shortcode tag.');
				return;
			}
			if (editor === 'unknown') {
				win.alert('WP-Juicebox is unable to determine the editor. Please copy and paste the Juicebox Gallery shortcode tag when instructed.');
			}

			var postContent = '';
			switch (editor) {
				case 'ckeditor':
					postContent = win.CKEDITOR.instances.content.getData();
					break;
				case 'fckeditor':
					postContent = win.FCKeditorAPI.GetInstance(id).GetData();
					break;
				case 'quickpress':
				case 'text':
					postContent = jQuery(win.edCanvas).val();
					break;
				case 'visual':
					postContent = win.tinyMCE.activeEditor.getContent();
					break;
				case 'codemirror':
				case 'unknown':
				default:
					break;
			}

			var matches = postContent.match(/\[juicebox.*?gallery_id="[1-9][0-9]*".*?\]/gi);
			var gallery = matches !== null ? matches.length : 0;

			if (gallery > 0) {
				var term = gallery > 1 ? gallery + ' Juicebox Gallery shortcode tags' : 'a Juicebox Gallery shortcode tag';
				win.alert('This ' + jbPostType + ' already contains ' + term + '.');
				return;
			}

			if (typeof this.configUrl !== 'string' || typeof win.tb_show !== 'function') {
				win.alert('WP-Juicebox is unable to display the gallery settings.');
				return;
			}

			var connector = /\?/.test(this.configUrl) ? '&amp;' : '?';
			var url = this.configUrl + connector + 'TB_iframe=true&amp;width=600&amp;height=400';
			win.tb_show('Add Juicebox Gallery', url , false);
		}
	};
}();

JB.Gallery.Generator = function() {

	var insertTag = function(tag) {

		tag = tag || '';

		var win = window.parent || window;

		var editor = jbGetEditor();
		var id = jbGetId();

		switch (editor) {
			case 'ckeditor':
				win.CKEDITOR.instances.content.insertText(tag);
				break;
			case 'fckeditor':
				win.FCKeditorAPI.GetInstance(id).InsertHtml(tag);
				break;
			case 'quickpress':
			case 'text':
				if (typeof win.QTags !== 'undefined' && typeof win.QTags.insertContent === 'function') {
					win.QTags.insertContent(tag);
				} else if (typeof win.edInsertContent === 'function') {
					win.edInsertContent(win.edCanvas, tag);
				} else {
					win.alert('WP-Juicebox is unable to insert a Juicebox Gallery shortcode tag.');
				}
				break;
			case 'visual':
				win.tinyMCE.activeEditor.focus();
				if (win.tinyMCE.isIE) {
					win.tinyMCE.activeEditor.selection.moveToBookmark(win.tinyMCE.EditorManager.activeEditor.windowManager.bookmark);
				}
				win.tinyMCE.activeEditor.execCommand('mceInsertContent', false, tag);
				break;
			case 'codemirror':
			case 'unknown':
			default:
				win.alert('Please copy and paste the Juicebox Gallery shortcode tag: ' + tag);
				break;
		}

	};

	return {

		initialize: function() {

			if (typeof jQuery === 'undefined') {
				return;
			}

			jQuery('#jb-add-gallery').click(function() {
				jQuery('#jb-add-gallery, #jb-add-cancel').prop('disabled', true);
				jQuery(':input', '#jb-add-gallery-form').not('.jb-button', '#jb-add-gallery-form').prop('disabled', false);
				jQuery.post(JB.Gallery.Generator.postUrl, jQuery('#jb-add-gallery-form').serialize(), function(data) {
					var dataInteger = parseInt(data, 10);
					if (!isNaN(dataInteger)) {
						var tag = '[juicebox gallery_id="' + Math.abs(dataInteger) + '"]';
						insertTag(tag);
					} else {
						var win = window.parent || window;
						win.alert('WP-Juicebox is unable to determine the gallery settings.');
					}
				}).fail(function() {
					var win = window.parent || window;
					win.alert('WP-Juicebox is unable to insert a Juicebox Gallery shortcode tag.');
				}).always(function() {
					var win = window.parent || window;
					win.tb_remove();
				});
			});

			jQuery('#jb-add-cancel').click(function() {
				jQuery('#jb-add-gallery, #jb-add-cancel').prop('disabled', true);
				var win = window.parent || window;
				win.tb_remove();
			});
		}
	};
}();
