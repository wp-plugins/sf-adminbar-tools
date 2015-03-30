=== SF Admin Bar Tools ===
Contributors: GregLone
Tags: admin, admin bar, bar, query, screen, tool
Requires at least: 3.1
Tested up to: 3.7
Stable tag: trunk
License: GPLv3
License URI: http://www.screenfeed.fr/gpl-v3.txt

Adds some small interesting tools to the admin bar for Developers.

== Description ==
The plugin adds a new tab in your admin bar with simple but useful indications and tools.
* Displays the number of queries in your page and the amount of time to generate the page. Click this item will retract the entire admin bar to the right. Why is it useful? Have you ever had some php notices hidden under the admin bar? You probably had, so now you understand the aim of this functionality.
* Displays php memory usage, php memory limit, and php version. Hover this item to display WP_DEBUG state and error reporting value.
**In your site front-end:**
* The php memory item: hover it to display the current template too.
* $wp_query: click the *$wp_query* item will open a lightbox with the content of $wp_query. Click its blue title to reload the lightbox, click outside the lightbox to close it. Since version 2, works on 404 pages too.
* Responsive admin bar: better handling for small screens.
**In your site administration:**
* Fix/unfix the admin menu: not really a tool, it gives you the opportunity to float the admin menu. I mean, in long pages, your menu will remain visible on screen: no more long scroll up to navigate into your administration area.
* Current screen: a dropdown containing lots of things.
1. Three lists of useful hooks (actions). The indicator to the right of the line tells you if the hook has been triggered (a "x" means the plugin doesn't know, because the hook occurs after the admin bar). Colors: green -> the hook occurs before the headers are sent, orange -> after the headers, red -> in footer. A "P" means the hook has a parameter: hover it for more infos. Click a hook (on its text) to auto-select its code, for example: click *admin_init* to select *add_action( 'admin_init', '' );*
2. $...now: this dropdown contains the value of the well-known variables $pagenow, $typenow and $taxnow.
3. Finally, you can know the current page id and base.

= New in version 2: cowork =
If you work with a team or other developers, one of the worst thing that could happen is if two developers edit the same file at the same time.
This is where the new Cowork functionality comes in. Once enabled (click the cowork button), you'll have a file tree listing all the files in the current theme and in the plugins directory. Click a file, it's yours, you can now go to your ftp program to edit this file. The file disappear from the file tree and appear in your personal list. For other users, the file will remain in place but it will be red and they won't be able to click it, they know now you are editing this file and they shouldn't. In the meantime in the main plugin item, you will have one or two counters: the grey one is the number of files open by you, the other is the total of files open by others.
Counters and file tree are refreshed frequently, depending of the situation: main item hover, window focus, every 5 minutes (counters only).
Once you finished your work, clicking on *Stop coworking* will clear your files list and stop your cowork session.
**A little warning**: with long lists, I hope you have a big screen ;) If you have really lots of files in your theme and your plugins directory, the plugin can be slow to insert the list in the admin bar and freeze the screen for few seconds.

You can decide who's gonna use this plugin. First thing to do after installing the plugin is to go to settings page and check the users (only administrators). This way, the plugin's items won't show up to other users (your client for example).

= Translations =
* English
* French

= Important note: browser requirement =
You'll need a modern browser to use correctly this plugin. I used CSS3 and HTML5 features without fallback to keep it simple. Why? I think developers should use a modern browser, so why should I bother myself with fallbacks? ;) (you don't work with a dinosaur, right?)
For example, the "retract admin bar" and "float admin menu" use localStorage to keep track of your preferences between pages.

== Installation ==

1. Extract the plugin folder from the downloaded ZIP file.
2. Upload the `sf-admin-bar-tools` folder to your *wp-content/plugins/* directory.
3. Activate the plugin from the "Plugins" page.

== Frequently Asked Questions ==

None, yet.
Check out [my blog post](http://www.screenfeed.fr/sf-abt2/) for more infos or tips (sorry guys, it's in French, but feel free to leave a comment in English if you need help).

== Screenshots ==

1. The admin bar item displaying the number of queries in your page and the amount of time to generate the page.
2. The admin bar retracted.
3. The dropdown with coworking enabled.
4. The admin bar in front-end on a small screen.
5. The hooks dropdowns.
6. Coworking: browse the files and indicate you're editing it.

== Changelog ==

= 2.1.1 =
* 2013/01/26
* Bugfix in settings page (a missing BR tag)

= 2.1 =
* 2013/01/26
* New: Auto "subscribe" when the plugin is activated. No need to rush to the settings page after activation now.
* New tool: `pre_print_r()`. It's a kind of improved `print_r()` to use where you need: wrap with a `<pre>` tag, choose how to display it (or not) to other users with 2 parameters.
* New: add your own options in the settings page. See the two action hooks 'sf-abt-settings' and 'sf-abt-preferences'. Now there's a new system to deal with the plugin options, see the 'sf_abt_default_options', 'sf_abt_sanitization_functions' and 'sf_abt_sanitize_settings' filters.
* New section "Personal preferences" in the plugin settings page, with the two following options:
* The cowork tree and statuses are refreshed every 5 minutes and on window focus. Now you can disable this.
* When you're on a post edit screen, WordPress autosave your post every minute. Now you can disable this.
* New: Enable the "All Options" options menu.
* Enhancement: if you use the Debug Bar plugin, its admin bar item has an icon on a small screen now (icon from http://gentleface.com/free_icon_set.html).
* Fix: in rares occasions, the admin submenus were displayed under content.
* Fix: use `wp_get_theme()` only if exists (WP 3.4).
* Fix: check WordPress version.

= 2.0.1 =
* 2012/10/17
* Bugfix in settings page

= 2.0 =
* 2012/10/16 - Major release
* Bugfix: jQuery is now launched correctly in themes where it's not already present.
* Enhancement: the main item is now located at the far right of the admin bar. I think it's more convenient for the "retract" functionality.
* Enhancement: now there's a small indicator for the "Fix/unfix admin menu" functionality.
* Enhancement: the $wp_query lightbox works on a 404 page.
* New tool: cowork.
* New indicators: php memory, php version, WP_DEBUG state, error_reporting level, current front-end template.
* New tool: hooks list in administration.
* Thanks a lot to juliobox for some of the awesome ideas :)

= 1.0.1 =
* 2012/06/16
* Minor CSS fix for WP 3.4: the floated admin menu was partially hidden under the admin bar.

= 1.0 =
* 2012/06/10 - First public release

== Upgrade Notice ==

Nothing special