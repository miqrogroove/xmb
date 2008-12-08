XMB 1.9.5 through 1.9.10 Upgrade Utility for XMB 1.9.11

For full details, please read the instructions in the Documentation folder.

Short version:

* Backup everything. Database, files, the lot.

Did you backup everything? Including config.php? You'll need that.

* Turn off your board using the Board Status setting in the Administration Panel.
* Configure the new config.php appropriately, filling in values from the old.
* Upload the entire upgrade folder to your forum folder, resulting in a upgrade/ folder
* Upload everything inside files/ to your forum folder, replacing the older files
* Skip or delete the install/ folder.
* Run http://www.example.com/forums/upgrade/upgrade.php Substituting "www.example.com/forums" for your forum's URL.
* Follow the prompts.

If it doesn't work, back it out by restoring from backups, and come see us at

http://forums.xmbforum.com/

Good luck!
XMBForum Team
