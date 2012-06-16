=== SF Admin Bar Tools ===
Contributors: GregLone
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=UBY2MY2J4YB7J&item_number=SF-Admin-Bar-Tools
Tags: admin, admin bar, bar, query, screen
Requires at least: 3.3
Tested up to: 3.4
Stable tag: trunk
License: GPLv3

Adds some small interesting tools to the admin bar for Developers.

== Description ==
The plugin adds a new tab in your admin bar with simple but useful indications and tools.
First, it displays the number of queries in your page and the amount of time to generate the page.
A click on this item will retract the admin bar to the right. Why is it usefull? Have you ever had some php notices hidden under the admin bar? You probably had, so now you understand the aim of this fonctionality.
*In your site administration:*
In the dropdown you'll see two things: the first item is not really a tool, it gives you the oppotunity to float the admin menu. I mean, in long pages, your menu will remain visible on screen: no more long scroll up to navigate into your administration area. The second thing the dropdown displays is the current screen id: "dashboard" when you are in the dashboard, "upload" for the media library, etc.
*In your site frontend:*
There's only one tool in the dropdown here: a click on the "$wp_query" item will open a lightbox with the content of $wp_query. Click on its blue title to reload the lightbox, click outside the lightbox to close it.

= Translations =
* English
* French

= Multisite =
* The plugin is ready for Multisite.

= Important note: browser requirement =
You'll need a modern browser to use correctly this plugin. I used CSS3 and HTML5 features without fallback to keep it simple. Why? I think developpers should use a modern browser, so why should I bother myself with fallbacks? ;) (you don't work with a dinosaur, right?)
For example, the "retract admin bar" and "float admin menu" use localStorage to keep track of your preferences between pages.

== Installation ==

1. Extract the plugin folder from the downloaded ZIP file.
2. Upload sf-admin-bar-tools folder to your *"/wp-content/plugins/"* directory.
3. Activate the plugin from the "Plugins" page.
4. Done!

== Frequently Asked Questions ==
Check out [my blog post](http://www.screenfeed.fr/sf-abt/) for more infos or tips (sorry guys, it's in french, but feel free to leave a comment).

== Screenshots ==
1. The admin bar item displaying the number of queries in your page and the amount of time to generate the page.
2. The admin bar retracted.
3. The dropdown in the administration area.
4. The dropdown in front-end area.
5. The $wp_query lightbox.

== Changelog ==

= 1.0.1 =
* 2012/06/16
* Minor CSS fix for WP 3.4: the floated admin menu was partially hidden under the admin bar.

= 1.0 =
* 2012/06/10
* First public release

== Upgrade Notice ==

Nothing special










































