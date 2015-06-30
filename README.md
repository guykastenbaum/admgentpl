## usage

add .htaccess in this admin directory, /gen/must be writable (at least for building)

point to gen/gentpl.php it is a config admin builder 

 - put login pass name for db access (builds db_conf.php)
 - select a table, creates default config (tab/tablename_inc.php)
 - edit table config (auto help) label, menu list or sql, type, sizes, etc
 - you can add html or php snippets in various parts of the page (not yet re-tested)
 - installs (just a copy of include gen/gen.php)
 - generate all for all tables (coool) at admin level gen/..

now the admin is complete, you can point to any generated file, edit template_admin, etc


## history

Started in 2005 (ten years after!) after magen(DQ).

It uses homemade libs for sql and php-array to file, and sql/tab menu.

It generated php files that could be reedited. 
I added tweaks in menu, and various other parts.

Latest rebuild is to compact the code, and make all in one double pass directly from the config. 
A first pass reads the config and html parts to build a full length html template file, this html is then templated with the data flow.

It used prototypes framework, so U had to switch to jquery an djquery-ui (I haven't recoded the ajax part;yet)
I could rerecode the front-end in angular.
