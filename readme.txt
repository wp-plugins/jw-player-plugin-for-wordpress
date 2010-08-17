=== JW Player Plugin for WordPress ===
Contributors: LongTail Video Inc.
Tags: JW Player, Video, Flash, LongTail Video, RTMP, Playlists, Audio, Image, HTML5
Requires at least: 2.8.6
Tested up to: 3.0.1
Stable tag: 1.2.0

This module is provided by LongTail Video Inc.  It enables you to configure and embed the JW Player for Flash for use on your WordPress website.

== Description ==

The JW Player Plugin for WordPress makes it extremely easy to deliver video through your WordPress website. This plugin has been developed by LongTail Video, the creator of the JW Player, and allows for easy customization and embedding of the JW Player in your WordPress posts. It provides support for all of the player's configuration options, including skins, plugins and the LongTail Video AdSolution.

In addition, it supports a powerful tag system that allows for dynamic customization at embed time, and gives you the capability of referencing external video content.  Plus, if your users are viewing your site on an iDevice, it will embed the <video> tag instead.  Allowing your users to experience your video content from anywhere.

This plugin also expands the built in WordPress Media Library.  You can now add media files from a URL (including full support for YouTube videos and RTMP streams).  It is also really simple to create custom playlists using the built in Playlist Manager.

For more information about the JW Player and the LongTail AdSolution please visit <a href='http://www.longtailvideo.com/?utm_source=WordPress&utm_medium=Product&utm_campaign=WordPress'>LongTail Video</a>.

If you have any questions, comments, problems or suggestions please post on our forum at http://www.longtailvideo.com/support/forums.

== Installation ==

1. Place the plugin folder in your plugin directory.
1. Download the player from www.longtailvideo.com.
1. Place player.swf and yt.swf into the JW Player Plugin for WordPress directory.
1. Navigate to Site Admin > Plugins.
1. Click the activate link to enable the plugin.
1. Click on Save configuration.

== Requirements ==

* WordPress 2.8.6 or higher
* PHP 5.0 or higher
* The "configs" directory contained in the plugin directory must be writable.

== Usage ==

1. Go to Site Admin > Settings > JW Player Plugin
1. Click on the button to create a player.
1. Configure the Basic flashvars.
1. (Optional) Configure Advanced flashvars and add plugins.
1. Save your Player.
1. Create or edit a post.
1. Insert the following tag: [jwplayer config="&lt;Player name&gt;" file="&lt;your video&gt;"] into the body.  &lt;your video&gt; is a url to your file.  The "config" attribute is only need when using a player other than the default.
1. Save your posts.
1. For more advanced and detailed description of the module please refer to the provided manual.

== Changelog ==

= 1.2.0 =
* FEATURE: Installation of the JW Player is now handled entirely within the plugin.  Simply click a button to install the JW PLayer.  Additionally, upload of licensed players can be done through the plugin as well.
* FEATURE: Content Aware Embed - when embedding audio without a thumbnail, only the controlbar will show
* FEATURE: RTMP Media Library support - can add RTMP streams to the media library and specify streamer and file at the media level. (plugin will make best guess at streamer and file on import)
* FEATURE: Should now be able to use the shortcode in widgets
* FEATURE: Will use the video tag (for .mp4) or youtube embed (for youtube videos) when your blog is viewed on an iPod, iPhone or iPad.
* ENHANCEMENT: Player and configs relocated to the WordPress uploads directly.  This should minimize file permission issues going forward.
* ENHANCEMENT: Image Embedding now officially supported - Can specify the duration flashvar on image media.
* ENHANCEMENT: Audio embedding now officially supported - Can specify the thumbnail flashvar on audio media
* ENHANCEMENT: Arbitrary flashvar field added - useful when using custom plugins and you need to specify flashvars that aren't listed in the JW Player Setup
* ENHANCEMENT: Plugin now uses .zip skins.  Number of available skins has been greatly expanded.
* ENHANCEMENT: Top Nav Bar has been added to the JW Player Setup wizard interface - should make specific edits to your players much easier now
* ENHANCEMENT: Significant redesign of the Playlist Manager - Improved usability and better handling of large media libraries.
* BUG: spaces removed from plugin list - should address unexpected plugin behavior (eg. LTAS not working correctly).
* BUG: Provider/Streamer flashvars have been added back to JW Player Setup
* BUG: Image editing should no longer fail while the plugin is active

= 1.1.2 =
* reimplemented path generation and usage
* Fixed links to longtailvideo.com
* Added links to plugin pages for plugins

= 1.1.1 =
* Improved path resolution.

= 1.1.0 =
* Fixes path resolution of player.swf on the LAMP stack.

= 1.0.0 =
* Initial release of the JW Player Plugin for WordPress
