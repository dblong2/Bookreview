=== Plugin Name ===
Contributors: philip.newcomer
Author: Philip Newcomer
Author URI: http://philipnewcomer.net
Donate link: http://philipnewcomer.net/donate/
Tags: rating, post, author, stars, custom post type, custom post types, post type
Version: 1.1.1
Stable tag: 1.1.1
Requires at least: 3.1
Tested up to: 3.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows a post author to assign a simple 1-5 star rating to a post, page, or custom post type, which will then be displayed on the post.

== Description ==

There are plenty of plugins available which allow site *visitors* to rate posts, but I didn't find any that gave the post *author* that functionality, so I wrote this plugin. *Author Post Ratings* adds a meta box to the post edit screen, allowing you to chose a 1-5 star rating for the post, or to leave it unrated. The plugin will automatically add the post rating (using stars, and an optional label) to the top or bottom of the post. If you wish, you can disable that functionality altogether and use a shortcode to insert the post rating anywhere in the post you choose. The plugin supports ratings for posts, pages, and custom post types, all of which can be individually enabled or disabled in the plugin settings. It is also fully internationalized, with Spanish language translation files included.

== Installation ==

*Author Post Ratings* is installed just like any other WordPress plugin. No configuration is required, but if you wish, you may change the plugin settings at Settings &gt; Author Post Ratings.

If you're not familiar with installing WordPress plugins, follow these steps to install the plugin:

= Automatic Installation =

1. Login to your WordPress dashboard, and go to Plugins &gt; Add New.
2. In the Search box, type in 'Author Post Ratings', and click 'Search Plugins'.
3. *Author Post Ratings* should be the first item in the list. Click on 'Install Now'.
4. WordPress will download and install the plugin. Click on the 'Activate Plugin' link, and you're in business!

= Manual Installation Via FTP =

1. Download the plugin to your computer, and unzip it.
2. Using an FTP program, upload the 'author-post-ratings' folder to your `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

= Manual Installation Via WordPress Upload =

1. Download the plugin ZIP file to your computer.
2. In your WordPress dashboard, go to Plugins &gt; Add New.
3. Click on the 'Upload' link at the top.
4. In the file selection box, select the plugin ZIP file on your computer, and click on 'Install Now'.
5. WordPress will upload and install the plugin. Click on the 'Activate Plugin' link, and you're in business!

== Frequently Asked Questions ==

= How do I customize the CSS of the post rating output? =

You can edit `author-post-ratings.css` in the plugin folder.

= I don't like the star graphics that come with the plugin, or I want to use a different color. Can I change them? =

Sure, just replace the two images in the `author-post-ratings/images/` directory with your own star images. You can also download the Photoshop source files for the supplied star images from the [plugin homepage][plugin homepage URL].

= Where do I get support for this plugin? =

Please use the plugin's support forum here on WordPress extend.

= Are there any functions I can use in my template files? =

Yes. You can use `get_author_post_rating()` and `the_author_post_rating()` in your templates. These functions are explained in more detail on the [plugin homepage][plugin homepage URL].

= This plugin is useful and I want to donate to the author. =

Thank you! Please head on over to my [donate page](http://philipnewcomer.net/donate/). Any donation, no matter how large or small, is greatly appreciated!

[plugin homepage URL]: http://philipnewcomer.net/wordpress-plugins/author-post-ratings/

== Translating the Plugin ==

The post rating label text can be changed in the plugin settings, so no translation is required for the frontend (public side) of the site. However, if you wish to translate the backend settings interface, the plugin is fully internationalized and ready for translation. There is a .po and a .mo file included in the plugin's 'languages' directory for your convenience.

The plugin includes the following translations:

* Spanish
_courtesy of [WebHostingHub](http://www.webhostinghub.com/)_.

== Screenshots ==

1. The post rating meta box which can be enabled for posts, pages, or custom post types
2. A post with an author post rating displayed in the Twenty-Eleven theme
3. The plugin settings

== Changelog ==

= 1.1.1 =
* Added Spanish language translation files, courtesy of [WebHostingHub](http://www.webhostinghub.com/). No changes to main plugin code.

= 1.1 =
* Added option to only show the rating in singular views, to hide the post rating on archive pages when the `<!--more-->` quicktag is used in place of true excerpts
* Updated readme.txt: clarified installation instructions, added documentation for new plugin setting, added translating section
* Updated screenshot of plugin settings page to show new settings field
* Changed the way the plugin handles its saved settings internally
* Updated language files

= 1.0 =
* Initial Release
