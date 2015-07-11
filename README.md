# Newstatpress development roadmap

**USELESS IDEA**
- add a page help somewhere (the help for the different %variable%,...)
- [ ] tab construction for overview page
- [ ] irihdate function in double with newstatpress_hdate => to clean
- [ ] Fix number of dot of navbar in Visitors page
- [ ] Add bot rss https://github.com/feedjira/feedjira/tree/master
- [ ] update options use of foreach
- [ ] change the name of widget dashboard
- [ ] Big problem on search function
- [ ] Export database > dump sql (bkp)
- [ ] add Select definitions to update
- [ ] Add a 'unique visitors' row in the overview chart
- [ ] Add options to (de)activate single chart/graphs of overview and details
- [ ] Add options to let or not users (not administrator) to see or not options/owerview/details/visits
- [ ] Generate extenal API interfaces
- [ ] Add'version' in external API
- [x] Change days calculation into prune functions
- [ ] add number of visitors online in the overview page
- [ ] add jquery for credit page
- [ ] Database migration routine with unique name

## 1.0.7
*Released date: 2015-*-*

- [ ] Updated Locale fr_FR, it_IT

## 1.0.7
*Released date: 2015-07-11

- [x] Fix %mvisits% not giving result
- [x] Add %wvisits% week visits
- [x] Fix capability problems created by https://codex.wordpress.org/Multisite_Network_Administration

## 1.0.6
*Released date: 2015-07-01

IMPORTANT UPDATE

- [x] Close a possible Reflected XSS attack (thanks to James H - g0blin Reserch)
- [x] Avoid MySQL error if erroneous input is given (thanks to James H - g0blin Reserch)

## 1.0.5
*Released date: 2015-06-30

IMPORTANT CRITICAL UPDATE
- [x] Close a XSS and a SQLI Injection involeved IMG tag (thanks to James H - g0blin Reserch)

## 1.0.4
*Released date: 2015-06-30

IMPORTANT CRITICAL UPDATE
- [x] Close a persistent XSS via HTTP-Header (Referer) (no authentication required) (thanks to Michael Kapfer - HSASec-Team)

## 1.0.3
*Released date: 2015-06-23

- [x] Fix nsp_DecodeURL code cleanup replacement
- [x] Fix NewStatPress_Print missing after cleanup

## 1.0.2
*Released date: 2015-06-21

User interface changes:
- [x] Added API key option in option menu
- [x] Added API activation option in option menu
- [x] Implement external API "version" (gives actual version of NewStatPress)
- [x] Added informations tabs in Tools menu ()
- [x] Updated General tab in Option menu ()
- [x] Updated Widgets title
- [x] Updated IP2nation option menu
- [x] Fixed Dashboard widget overflow

Core changes:
- [x] Fix the plugin menu view for "subscriver"
- [x] Fix IP2nation database installation bug
- [x] Remove IP2nation download function (to be best conform with WP policy)
- [x] Massive code cleaning to avoid conflict with others plugins
- [x] Added bots (+7, thanks to Nahuel)
- [x] Updated Locale fr_FR, it_IT

## 1.0.1
*Released date: 2015-06-08

IMPORTANT CRITICAL UPDATE
- [x] Close a SQL injection (Thanks to White Fir Design for discover and communicate). Actually the old Statpress search code seems to be sanitized all.

## 1.0.0
*Released date: 2015-05-29*

Core changes:
- [x] Remove %installed% variable

## 0.9.9
Released date: 2015-05-20

IMPORTANT CRITICAL UPDATE
- [x] Close a XSS and a SQL injection and possible other more complex to achieve (thanks to Adrián M. F. for discover and communicate them). Those are inside the search routine from Statpress so ALL previous versions of Newstatpress are vulnerable (and maybe they are present in lot of Statpress based plugin and Statpress itself).
- [x] Fix missing browser images
- [x] Add tools for optimize and repair the statpress table
- [x] Updated Locale it_IT

## 0.9.8
Released date: 2015-04-26

- [x] Fix missing routine for update
- [x] Fix cs_CZ translation

## 0.9.7
Released date: 2015-04-11

User interface changes:
- [x] Added New option in Overview Tab : overview stats calculation method (global distinct ip OR sum of each day) (Note: online for month at the moment)
- [x] Added New options in General Tab : add capabilities selection to display menus, options menu need to be administrator by default
- [x] Added New information 'Visitors RSS Feeds' in Overview page
- [x] Updated Locale fr_FR, it_IT, cs_CZ

Core changes:
- [x] Updated OS definition
- [x] Updated Browser definition
- [x] Fixed '3 months Purge' issue


## 0.9.6
Released date: 2015-02-21

User interface changes:
- [x] Added Option page with tab navigation (use jQuery and idTabs)
- [x] Fixed Search page link
- [x] Updated Locale fr_FR, it_IT

Core changes:
- [x] Various fixes (global definition, function, plugin page names with nsp_ prefix, code spliting)
- [x] Various debug fixes (deprecated function, unset variable)
- [x] Fixed %thistotalvisit% in API call


## 0.9.5
Released date: 18/02/2015

- [x] Fixed PHP compatibility issue on old versions (tools page)

## 0.9.4
Released date: 18/02/2015

User interface changes:
- [x] Added Tool page with tab navigation
- [x] Added variable informations in Widget 'NewStatPress'
- [x] Fix Overview Table (CSS)
- [x] Updated Locale fr_FR, it_IT

Core changes:
- [x] Update of Widget 'NewStatPress' : code re-writed


## 0.9.3
Released date: 17/02/2015

- [x] Add Visits page with tab navigation
- [x] Add tab navigation in Crédits page
- [x] Add 'Donator' tab in Crédits page
- [x] Add 'visits' and 'options' links in Dashboard widget
- [x] Add CSS style to navbar in Visitors page
- [x] Add colored variation in overview table
- [x] Re-writed Overview function
- [x] Fix Duplicate INDEX when User database is updated (function rewrited)
- [x] Fix dashboard 'details' dead link
- [x] Fix navbar dead link in visitors page
- [x] Various code fixing
- [x] Api for variables (10x faster to load page with widget)
- [x] Changelog sort by last version
- [x] Update: locale fr_FR, it_IT

## 0.9.2
Released date: 09/02/2015

- [x] CSS fix, Overview fix and wp_enqueue_style compatibility fix

## 0.9.1
Released date: 08/02/2015

- [x] Activate changes of 0.8.9 in version 0.9.1 with PHP fixes

## 0.9.0
Released date: 07/02/2015

- [x] Revert to version 0.8.8 for problems with old PHP version

## 0.8.9
Released date: 07/02/2015

Development:
- [x] Add Ip2nation download function in option page
- [x] Add plugin homepage link, news feeds link, bouton donation in credit page
- [x] Add CSS style to stylesheet (./css/style.css), partially done
  - [x] remove page
  - [x] update page
  - [x] credit page
- [x] Optimization of the option page
- [x] Optimization of the credit page
- [x] Optimization of the export page
- [x] Optimization of the remove page
- [x] Optimization of the database update page
- [x] Fixed 'selected sub-menu' bug
- [x] Fixed wrong path to update IP2nation when database is updated (/includes)
- [x] Add variables %yvisits% (yesterday visits) %mvisits% (month visits)
- [x] Fix 5 bots, add 13 new bots


Translation Update:
- [x] fr_FR
- [x] it_IT
