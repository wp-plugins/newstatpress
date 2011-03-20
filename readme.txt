=== Plugin Name ===
Contributors: ice00
Donate link: http://newstatpress.altervista.org
Tags: stats,statistics,widget,admin,sidebar,visits,visitors,pageview,user,agent,referrer,post,posts,spy,statistiche,ip2nation,country
Requires at least: 2.1
Tested up to: 3.1
Stable Tag: 0.1.0

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

Now you could add these values everywhere! NewStatPress >=0.7.6 offers a new PHP function *NewStatPress_Print()*.
* i.e. NewStatPress_Print("%totalvisits% total visits.");


== Installation ==

Upload "newstatpress" directory in wp-content/plugins/ . Then just activate it on your plugin management page.
You are ready!!!


= Update =

* Deactivate StatPress plugin (no data lost!)
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

== Updates ==

*Version 0.1.0 (19/03/2011)

* Adds index onto Statpress 1.4.1 table for improve velocity
* Changes data type of some fields for saving space
* Let the images to be visible even for relocated blog
* Makes the update of search engine more quick
