XMB 1.9.1 Upgrade Utility

*** NOTICE ***

Make sure to always perform these steps before using *ANY* upgrade script!

* backup all the data in your database 
* backup all your forum's files 

Although this is common sense, it's often forgotten. 


Database Hacks 

!!!    This upgrade is designed to work when hacks are installed.   !!!
!!!      However, hack data will be destroyed by this utility       !!!
!!! Proceed with extreme caution if you want to keep your hack data !!!

*** END NOTICE ***


Compatibility

Any version of XMB 1.11c or higher 
- It does not work with earlier versions.


Required Files:

* upgrade.lib.php
* upgrade.php
* install.css
* XMB_*.xmb (at the time of writing, XMB_1_9_1.xmb)

createFile.php script should not be uploaded to your system.
This script creates the schema for XMB developers.


Upgrade Instructions 

Disable posting to your board in Admin -> Settings

0. Backup your database. 

	*** This requires columns and data to be backquoted. *** 
	
   The following invocation from the command line is known to work: 
   
   mysqldump --opt --quote-names databasename -u user -p password > backup.sql
   
   If you're using myPHPAdmin, you must tick 
   
	* Add drop table
	* Add AUTO_INCREMENT value
	* Enclose table and field names with back quotes
	
	* "Extended inserts" for small databases (up to 5 MB per table) or "Complete inserts" for
	  larger databases ( > 5 MB for larger tables, such as xmb_attachments )
	
   myPHPAdmin is not good for backing up larger databases - it sometimes has a 50 MB maximum size 
   limit and downloads to your browser rather than on your host. 
   
   Therefore, backing up using myPHPAdmin is only appropriate for those on faster connections or 
   if you have less than 50 MB of data.
   
1. Backup all your forum's files
2. Upload all of Nexus 1.9's files over the top of your existing forum files
3. Delete ./install/
4. Edit the new config.php to suit your needs
5. Upload upgrade.php, upgrade.lib.php, install.css to your forum
6. Run http://www.yourdomain.com/pathtoyourforum/upgrade.php
7. When it succeeds, check that upgrade.php, upgrade.lib.php, install.css, and xmb_*.xmb have 
   been deleted.
8. Log on your shiny new 1.9.1 board 
9. Turn on the board in Admin -> Settings. Adjust other new settings as you see fit. 


Restoring the data

If things go really badly, you will want to back out the changes. Luckily, this is pretty simple. 
Take the file you made in step 0 above, fire up the mysql command line tool, and type:

	use databasename;
	source backup.sql;
	
Or in myPHPadmin, choose your database and import the data in the SQL tab. Again, the 50 MB
file size limit might bite you.


Keeping XMB healthy

If your database is not looked after regularly, it can slow down. XMB 1.9.1 has many performance
improvements which can be best utilized with a healthy database. XMB recommends administrators 
use Admin -> Analyze on their databases weekly, followed by Admin -> Optimize. This keeps your 
board in the best possible shape. Try it now!


Basic system administration

You should find out if your hoster does backups for you. If they don't, you need to take backups
manually. XMB 1.9 has a backup utility, but it is best if you use your hoster's utilities (such as 
myPhpAdmin or the mysqldump tool for this purpose. Backups should be taken about once a week, and
more often for very busy sites.


Security

All software has bugs, XMB included. You should check back with http://www.xmbforum.com/ regularly
to see if you need to install patches. When customizing your board, remember the following:

* Changes to themes are supported
* Modifications to XMB's PHP files, or to the database schema, or altering templates within the 
  Admin -> Templates area makes it much harder for *YOU* to upgrade to the next service pack or a
  later version of XMB. 

Try to keep the number of hacks down, and consider using "diff" to allow you to reapply them if you need
to reload XMB from scratch. 


Known issues

The upgrade is quite resource-intensive, especially with large databases. 
  - The script might time out or look to be inactive 
  - The script can consume large amounts of disk space, up to twice the amount you are currently
  - using. Make sure you have enough room!
