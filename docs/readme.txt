----------#--- XMB README ---#----------

XMB (eXtreme Message Board) is a project of the XMB Development Team.
webfr34k began work on this script in January 2001, but eventually left XMB to move onto other thigns, Revres Xunil was put in charge upon his leaving with WJ, and SurfiChris backing him up. The staff has cahgned a few tiems, lang development become focused on by special language developers, and graphics work was given to specific people. Several RC's of the 1.5 version have been recently made, the latest beign RC5 which was the origianal code modified by the original authors with Javaman1 and dine, to fix all of the known bugs. 


Installation:
1. Open up config.php (Notepad will do) and find the 4 variables at the top ($dbname, $dbuser, $dbpw, $dbhost). Edit these so that they match the settings for your host.This allows you to set a prefix for the name of each table so you can install multiple instances of XMB on the same database.


2. Upload everything, retaining the original structure of the files. Everything thing should be in ASCII except the images/ content (binary).


4. Then, run the install script . To do this, just visit www.yourdomain.com/boarddir/install.php. The script will automatically install it.


5. Go to your boards and register. It will automatically make you an admin.


6. Visit the control panel (the link will be visible once your logged in) and change stuff. Enjoy!


The XMB DevTeam Thanks you for choosing XMB.
If you encounter any problems please visit us at http://forums.xmbforum.com/xmb/ or just stop in and say hey :)

DEV Note: XMB seems to not work properly with Apache 2.0, as this is beta software, nothign is being done to fix that (Apache being the beta software, though we are too but, it's their betaness that is making us not fix this.)