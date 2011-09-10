=== Plugin Name ===
Contributors: ice00
Donate link: http://newstatpress.altervista.org
Tags: stats,statistics,widget,admin,sidebar,visits,visitors,pageview,user,agent,referrer,post,posts,spy,statistiche,ip2nation,country
Requires at least: 2.1
Tested up to: 3.2.1
Stable Tag: 0.1.9

NewStatPress is a new version of StatPress (that was the first real-time plugin dedicated to the management of statistics about blog visits).

== Description ==

A real-time plugin dedicated to the management of statistics about blog visits. It collects information about visitors, spiders, search keywords, feeds, browsers etc.

This project borned for improving the Daniele Lippi's StarPress plugin adding a new history features and make it less db consuming.

Once the plugin NewStatPress has been activated it immediately starts to collect statistics information.
Using NewStatPress you could spy your visitors while they are surfing your blog or check which are the preferred pages, posts and categories.
In the Dashboard menu you will find the NewStatPress page where you could look up the statistics (overview or detailed).
NewStatPress also includes a widget one can possibly add to a sidebar (or easy PHP code if you can't use widgets!).

Note: you must disable the original StatPress plugin when activating this, as it use the same table of StatPress for storing data in DB (copy the data to another table will be very space consuming for your site, so it was better to use the same table)

= Support =

Check at  http://newstatpress.altervista.org

= What's new? =

Simple adding index to database and changes some data fields for better database storing (from here http://www.poundbangwhack.com/2010/07/03/improve-the-performance-of-the-wordpress-plugin-statpress-and-your-blog/ where some modification comes from)

= Ban IP =

You could ban IP list from stats editing def/banips.dat file.

= DB Table maintenance =

NewStatPress can automatically delete older records to allow the insertion of newer records when limited space is present.
This features is left as original StatPress but it will be replaced by the history data instead.

= NewStatPress Widget / NewStatPress_Print function =

Widget is customizable. These are the available variables:

* %thistotalvisits% - this page, total visits
* %alltotalvisits% - all page, total visits
* %totalpageviews% - total pages view 
* %since% - Date of the first hit
* %visits% - Today visits
* %totalvisits% - Total visits
* %os% - Operative system
* %browser% - Browser
* %ip% - IP address
* %visitorsonline% - Counts all online visitors
* %usersonline% - Counts logged online visitors
* %toppost% - The most viewed Post
* %topbrowser% - The most used Browser
* %topos% - The most used O.S.

Now you could add these values everywhere! NewStatPress offers a new PHP function *NewStatPress_Print()*.
* i.e. NewStatPress_Print("%totalvisits% total visits.");

New sperimental functions: place this command [NewStatPress: xxx] every were in your Wordpress blog pages and you will have the graph about the xxx function.

Available functions are:
 *  [NewStatPress: Top days]
 *  [NewStatPress: O.S.] 
 *  [NewStatPress: Browser]
 *  [NewStatPress: Feeds]
 *  [NewStatPress: Search Engine]
 *  [NewStatPress: Search terms]
 *  [NewStatPress: Top referrer]
 *  [NewStatPress: Languages]
 *  [NewStatPress: Spider]
 *  [NewStatPress: Top Pages]
 *  [NewStatPress: Top Days - Unique visitors]
 *  [NewStatPress: Top Days - Pageviews]
 *  [NewStatPress: Top IPs - Pageviews]

== Installation ==

Upload "newstatpress" directory in wp-content/plugins/ . Then just activate it on your plugin management page.
You are ready!!!


= Update =

* Deactivate NewStatPress plugin (no data lost!)
* Backup ALL your data
* Backup your custom DEFs files
* Override "newstatpress" directory in wp-content/plugins/
* Restore your custom DEFs files
* Re-activate it on your plugin management page
* In the Dashboard click "NewStatPress", then "NewStatPressUpdate" and wait until it will add/update db's content

== Frequently Asked Questions ==

= I've a problem. Where can I get help? =

Check at http://newstatpress.altervista.org

== Screenshots ==

Check at http://newstatpress.altervista.org

== Changelog ==

= 0.1.0 =

* Adds index onto Statpress 1.4.1 table for improve velocity
* Changes data type of some fields for saving space
* Let the images to be visible even for relocated blog
* Makes the update of search engine more quick

= 0.1.1 =

* Reactivate translactions
* Add more OS (MacOSX variants, Android)
* Add more Browser (Firefox 4, IE 9)

= 0.1.2 =

* Add images for new browser
* Better polish translation by Pawel Dworniak
* Separate iPhone/iPad/iPod devices

= 0.1.3 =

* Reactivate visitors/user online with unix timestamp

= 0.1.4 =

* Fix fromDate calculation

= 0.1.5 =

* Open link in new tab/window (thanks to Sisko)
* New displays of data for spy function (thanks to Sisko)
* Added %alltotalvisits%

= 0.1.6 =

* Add option for not track given IPs (from wp_slimstat)
* update Italian translation

= 0.1.7 =

* Let Search function to works again (thank to Ladislav)

= 0.1.8 =

* Add option for not track given permalinks (from wp_slimstat)

= 0.1.9 =

* make all reports in details to have the number of entries you want
* Add [NewStatPress: xxx] experimantal function for having report into wordpress page
* Add %totalpageviews% - total pages view


== Upgrade Notice ==

= 0.1.0 =

* relased 19/03/2011

= 0.1.1 =

* relased 22/03/2011

= 0.1.2 =

* relased 23/03/2011

= 0.1.3 =

* relased 23/04/2011

= 0.1.4 =

* relased 24/04/2011

= 0.1.5 =

* relased 12/05/2011

= 0.1.6 =

* relased 15/05/2011

= 0.1.7 =

* relased 29/05/2011

= 0.1.8 =

* relased 23/06/2011

= 0.1.9 =

* relased 10/09/2011