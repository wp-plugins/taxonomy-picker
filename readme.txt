=== Taxonomy Picker ===

Contributors: Kate Phizackerley, katephiz
Plugin Name: Taxonomy Picker
Plugin URI: http://www.squidoo.com/taxonomy-picker-wordpress-plugin
Tags: wp, query, taxonomy, category, categories, widget, plugin, sidebar, search
Author URI: http://katephizackerley.wordpress.com
Author: Kate Phizackerley
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: trunk
Version: 1.13.2

Add a widget to help your readers build custom queries using drop downs of your categories and custom taxonomies.  The latest version comes with a fully optional set of pre-packed custom taxonomies for anybody who wants additional "cartegories" or sets of "tags" without coding - the names are customisable.

== Description ==

Taxonomy Picker is a widget which you can include in your sidebar to help visitors build complex queries using categories and your custom taxonomies by chosing terms from drop down boxes.  The widget also includes a text search making it easy to search for text only within certain categories or taxonomies.

Results will be displayed using your theme's standard search form so the results need no additonal styling - but your permalinks must handle standard WordPress queries in the URL and some prettylink settings may be incompatible.

For example on my site I use it to allow users to:

+ Find all posts containing the word Egypt within the Books category
+ Find all posts within the Magazine category which match "Valley of the Kings" in my "Where?" custom taxonomy which also include the word Tutankhamun

You can display the query the reader selected from within code by using echo tpicker_query_string();


*Plugin home:* http://www.squidoo.com/taxonomy-picker-wordpress-plugin

== Installation ==

Download the and activate the plugin the usual way.  The new widget will then be immediately availble to use.

+ Upload files to the /wp-content/plugins/ directory
+ Activate the Taxonomy Picker plugin through the 'Plugins' menu in WordPress


== Upgrade Notice ==
= 1.13.1 =
Extends multi select option to basic, legacy widget
= 1.13.0 =
Adds experimental multi select option to beta widget (and to basic widget via filters).  Fixes notice warning.
= 1.12.0 =
Restructure Silverghll library code in preparation for release of Colophon plugin



== Screenshots ==

See  http://www.squidoo.com/taxonomy-picker-wordpress-plugin

== Changelog ==

1.13.0 Adds experimental multi-select option to beta widget
1.10.9 Fixes bug with tags; adds tpicker_query_string() function
1.5  Admin screen.  Post count.  Remember query.  Better handling of "all" option.
1.4  Bug fixes
1.3  Sorted multiple words in plain text search
1.2  Fixed accents in taxonomies
1.12 Incompatibility with WP3.1 addressed
1.01 PHP warning removed

== Frequently Asked Questions ==

See  http://www.squidoo.com/taxonomy-picker-wordpress-plugin


