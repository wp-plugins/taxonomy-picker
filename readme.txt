=== Plugin Name ===

Contributors: Kate Phizackerley
Plugin Name: Taxonomy Picker
Plugin URI: http://www.squidoo.com/taxonomy-picker-wordpress-plugin
Tags: wp, query, taxonomy, category, categories, widget, plugin, sidebar, search
Author URI: http://katephizackerley.wordpress.com
Author: Kate Phizackerley
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: trunk
Version: 1.10.3

Add a widget to help your readers build custom queries using drop downs of your categories and custom taxonomies.  The latest version comes with a fully optional set of pre-packed custom taxonomies for anybody who wants additional "cartegories" or sets of "tags" without coding - the names are customisable.

== Description ==

Taxonomy Picker is a widget which you can include in your sidebar to help visitors build complex queries using categories and your custom taxonomies by chosing terms from drop down boxes.  The widget also includes a text search making it easy to search for text only within certain categories or taxonomies.

Results will be displayed using your theme's standard search form so the results need no additonal styling - but your permalinks must handle standard WordPress queries in the URL and some prettylink settings may be incompatible.

For example on my site I use it to allow users to:

+ Find all posts containing the word Egypt within the Books category
+ Find all posts within the Magazine category which match "Valley of the Kings" in my "Where?" custom taxonomy which also include the word Tutankhamun


*Plugin home:* http://www.squidoo.com/taxonomy-picker-wordpress-plugin

== Installation ==

Download the and activate the plugin the usual way.  The new widget will then be immediately availble to use.

+ Upload files to the /wp-content/plugins/ directory
+ Activate the Taxonomy Picker plugin through the 'Plugins' menu in WordPress


== Upgrade Notice ==

= 1.10 =

Adds a beta version of an enhanced widget, which may be optionally enabled, to enable testing on a wider range of sites - see the plugin home for detils.  There is no reason to upgrade if you don't want to play with the new features; and no harm if you do as they are only enabled if you chose to.

In a nutshell, it adds the first pass at drill down.  

= 1.9x =

Technical release.Recoding of widget display in preparation for further development.  Various patch releases.

= 1.8 =

Extend widget options.

= 1.7.3=

Add option to change text for "Search"

= 1.7.2 =

Minor bug fixes.

= 1.7 = 

Override on the ** all ** option for international users

= 1.6 =

Technical changes.  Not released.

= 1.5 =

The plugin now comes with an options screen in Admin which allows you to define:

1) Whether the query is remembered - if you select this option then the combox boxes will populate with the query which has just been run (so long as the widget is displayed on the results page, e.g. is in a sidebar throughout your site).

2) The text used for ** All **. This includes using the taxonomy names as an options so if your taxonomies are Size and Color then you could have All Sizes and All Colors instead of the ** All ** used by the first version.

3) Whether to show the count alongside items. This isn't recommended (yet) for very large sites as I am not caching the query. That will come in v1.6 It works just fine and should be sufficient unless you have hundreds of posts and many entries in your taxonomies. So v1.5 good for hobby sites but if you are running an online shop you might prefer to wait for v1.6 for performance reasons - try it and see.

4) There was a problem that when ** All ** was selected everywhere that it depended to display a 404 Not Found on some sites. You can now specify the URL to use "URL if no selection" to use when ** All ** is selected for all options. Pick whatever best suits your site.

== Screenshots ==

See  http://www.squidoo.com/taxonomy-picker-wordpress-plugin

== Changelog ==
1.5  Admin screen.  Post count.  Remember query.  Better handling of "all" option.
1.4  Bug fixes
1.3  Sorted multiple words in plain text search
1.2  Fixed accents in taxonomies
1.12 Incompatibility with WP3.1 addressed
1.01 PHP warning removed

== Frequently Asked Questions ==

See  http://www.squidoo.com/taxonomy-picker-wordpress-plugin


