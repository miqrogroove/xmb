XMB 1.9 Upgrade Utility

------------------
IMPORTANT:

If you are upgrading from XMB 1.9.9 you only need to update the XMB templates
by importing them using the Templates option in the admin panel.  It is not
necessary to run any other utilities after copying the new files to your server.

If you are upgrading from XMB 1.9.8 SP2 or SP3 you have the option of running
the faster and more reliable upgrade.php utility included in the Files folder.

This Upgrade Utility is designed specifically to upgrade XMB versions lower
than 1.9.8 SP2.
------------------

For full details, please read the instructions in the Documentation folder.

Short version:

* Backup everything. Database, files, the lot.

Did you backup everything? Including config.php? You'll need that.

* Upload the entire upgrade folder to your forum folder, resulting in a upgrade/ folder
* Upload everything inside files/ to your forum folder, replacing the older files
* Configure the new config.php appropriately, filling in values from the old.
* Run

http://www.example.com/forums/upgrade/upgrade.php

Substituting "www.example.com/forums" for your forum's URL.

* Follow the prompts.

If it doesn't work, back it out by restoring from backups, and come see us at

http://forums.xmbforum.com/

Good luck!
XMBForum Team
