=== WP-Juicebox ===
Contributors: juicebox
Tags: Juicebox, photos, photo, images, image, posts, post, pages, page, plugin, gallery, galleries, media
Requires at least: 2.8
Tested up to: 4.8.1
Stable tag: 1.5.1

Allows you to easily create Juicebox galleries with WordPress.

== Description ==

The WP-Juicebox plugin allows you to easily create [Juicebox](https://www.juicebox.net/) galleries with WordPress. Juicebox is a free, customizable image gallery. Images and captions can be loaded from the WordPress Media Library or from Flickr.

Get full instructions and support at the [WP-Juicebox Homepage](https://www.juicebox.net/support/wp-juicebox/).

== Installation ==

= Installation =

1. Download the WP-Juicebox plugin. Unzip the plugin folder on your local machine.
2. Upload the complete plugin folder into your WordPress blog's '/wp-content/plugins/' directory.
3. Activate the plugin through the 'Plugins' menu in WordPress
4. If the the '/wp-content/uploads/juicebox/' folder does not exist, create it and give it write permissions (755) using an FTP program.

= Requirements =

Before installing, please confirm your web server meets the following requirements. If you are not sure, contact your web host tech support.

* WordPress v2.8 or higher.
* PHP v5.2.0 or higher.
* The '/wp-content/uploads/juicebox/' folder must exist and have write permissions (755).
* PHP DOM extension is enabled (this is the default, however some web hosts may disable this extension).
* Active theme must call the [wp_head](http://codex.wordpress.org/Plugin_API/Action_Reference/wp_head) function in it's header.php file.

== Changelog ==

= 1.5.1 =
* Upgraded Juicebox-Lite to v1.5.1

= 1.5.0.1 =
* Fixed problem with non-default permalinks

= 1.5.0 =
* Upgraded Juicebox-Lite to v1.5.0
* Fixed gallery settings window style
* Improved error handling

= 1.4.4.2 =
* Upgraded Juicebox-Lite to v1.4.4.2

= 1.4.4.1 =
* Upgraded Juicebox-Lite to v1.4.4.1

= 1.4.4.0 =
* Upgraded Juicebox-Lite to v1.4.4

= 1.4.3.1 =
* Fixed bug whereby 'NextGEN Gallery' galleries did not display
* Ensured code is not added to external pages by plugins or themes

= 1.4.3.0 =
* Upgraded Juicebox-Lite to v1.4.3
* Added ability to copy and paste shortcode tag into body of page or post when using an unsupported editor
* Allowed custom media button to be displayed only within backend pages
* Allowed use of the Juicebox-Pro API by assigning variable name to Juicebox object
* Ensured JavaScript file is enqueued correctly within frontend pages
* Improved error notification when WordPress nonce verification fails
* Improved handling of multi-line image titles and image captions
* Replaced deprecated code with updated equivalent

= 1.4.2.1 =
* Fixed bug whereby defaults could not be set and galleries could not be edited in WebKit browsers
* Fixed bug whereby 'NextGEN Gallery' and 'Picasa Web Album' galleries did not display
* Fixed bug whereby submission could be performed remotely

= 1.4.2.0 =
* Upgraded Juicebox-Lite to v1.4.2
* Added support for using Picasa Album Id as well as Picasa Album Name
* Fixed 'PHP Notice' in WordPress Debug Mode
* Improved gallery settings window layout
* Removed deprecated code

= 1.4.1.0 =
* Upgraded Juicebox-Lite to v1.4.1
* Fixed 'PHP Notice' in WordPress Debug Mode
* Ensured all external CSS and JavaScript files are loaded only when required

= 1.4.0.1 =
* Fixed bug whereby XML data was not generated dynamically under certain WordPress installations

= 1.4.0.0 =
* Upgraded Juicebox-Lite to v1.4.0

= 1.3.3.1 =
* Added support for adding gallery in QuickPress widget on Dashboard page via media button
* Added support for CKEditor and FCKEditor
* Added support for reversing image order for 'Media Library' galleries
* Added tooltip text on 'Manage Galleries' page
* Fixed bug whereby default Pro Options appeared in gallery Pro Options text area in edit gallery form
* Fixed bug whereby Picasa Web Album did not display if Picasa User Id or Picasa Album Name contained whitespace
* Fixed HTML errors on 'Manage Galleries' page
* Fixed server paths in backup and restore Pro 'jbcore' folder routines
* Fixed visual input field issues in WebKit browsers
* Ensured all XML files use UTF-8 encoding
* Ensured only accepted images are included in galleries
* Ensured only required attributes are included in dynamically generated XML files
* Ensured only required resource files are included in administration pages
* Ensured only single Juicebox shortcode tag is inserted in page or post when user clicks 'Add Gallery' button multiple times
* Improved support for WordPress capabilities
* Improved error handling when unable to include Juicebox shortcode in page or post
* Removed deprecated code
* Clean numeric gallery configuration option values
* Disabled non-layout and gallery-specific options on 'Set Default Values' page
* Moved all inline CSS to external stylesheets
* Optimized code

= 1.3.3.0 =
* Upgraded Juicebox-Lite to v1.3.3

= 1.3.2.0 =
* Upgraded Juicebox-Lite to v1.3.2
* Clean color and opacity values
* Removed 'default.xml' file
* Optimized code

= 1.3.1.0 =
* Upgraded Juicebox-Lite to v1.3.1
* Lite Options from the Pro Options text area are no longer entered into the output XML files

= 1.3.0.0 =
* Upgraded Juicebox-Lite to v1.3.0
* Added support for new Juicebox-Lite configuration options textColor, thumbFrameColor, useFullscreenExpand and useThumbDots
* Custom default values no longer overwritten when updating plugin
* Media Library gallery message displays 'Upload/Insert' or 'Add Media' depending on version of WordPress installed
* Fixed bug whereby plugin does not activate successfully under certain conditions
* Fixed bug whereby gallery does not display under certain conditions

= 1.2.0.1 =
* Added support for 'Include Featured Image' in 'Media Library' galleries
* Fixed bug preventing Dashboard menu links from being displayed in certain installations
* Pro Options are now case-insensitive
* Removed <meta> 'viewport' tag from <head> section
* 'Delete All Galleries' button changed to 'Delete All Data' as button now cleanly removes all plugin-related data rather than just resetting options

= 1.2.0 =
* Upgraded Juicebox-Lite to v1.2.0
* Added support for 'Picasa Web Album' as source of images
* Added support for WordPress installations on https:// secure servers
* XML file now created dynamically so no need to edit gallery or post to rebuild static XML file
* Made distinction between pages and posts throughout plugin
* Gallery Id displayed in 'Add Juicebox Gallery' pop-up window
* Fixed bug allowing multiple gallery shortcodes to be entered into each post
* Fixed bug whereby duplicate calls were made to certain methods
* Fixed bug whereby corrupt NextGEN Gallery installation caused NextGEN-sourced gallery to fail
* Fixed 'PHP Deprecated' message
* Fixed 'PHP Notice' in WordPress Debug Mode
* Fixed W3C Markup Validation issue on admin page
* Fixed compatibility issue with WordPress v3.5 Beta 2

= 1.1.1 =
* Upgraded Juicebox-Lite to v1.1.1
* Added support for 'NextGEN Gallery' as source of images
* Added ability to delete all galleries and reset Gallery Id to zero
* Added ability to set/reset default values for gallery options
* Improved and restructured code
* Bugfixes

= 1.1.0 =
* Upgraded Juicebox-Lite to v1.1.0
* Improved escaping of XML entities
* Fixed bug whereby phantom XML file could be generated
* Fixed bug relating to single quote in gallery title
* Fixed bug causing error message when XML file does not exist
* Fixed bug causing error message with incorrectly formatted Pro Options
* Fixed compatibility issues with other plugins
* Scripts now called inside appropriate hooks
* Removed redundant code

= 1.0.2 =
* Upgraded Juicebox-Lite to v1.0.2

= 1.0.1 =
* Initial release

== Upgrade to Juicebox-Pro ==

[Juicebox-Pro](https://www.juicebox.net/download/) supports advanced customization options, no branding, unlimited images and more. To upgrade the WP-Juicebox plugin to Juicebox-Pro, [check here](https://www.juicebox.net/support/wp-juicebox/#pro).

== Credits ==

WP-Juicebox developed by [Juicebox](https://www.juicebox.net/).

== Terms Of Use ==

WP-Juicebox may be used for personal and/or commercial projects. [View Terms of Use](https://www.juicebox.net/terms/)
