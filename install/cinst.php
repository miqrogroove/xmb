<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

/**
 * Database table installation logic.
 *
 * @since 1.10.00
 */
class Install
{
    public function __construct(
        private DBStuff $db,
        private Password $password,
        private Schema $schema,
        private SiteData $site,
        private SQL $sql,
        private UpgradeOutput $show,
        private Variables $vars,
    ) {
        // Property promotion.
    }

    public function go()
    {
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        ob_implicit_flush(true);

        $this->show->progress("Checking Super Administrator Account");
        $iUsername = strtolower($this->site->adminUser);

        if ($this->site->adminEmail == '' || $this->site->adminPass == '' || $this->site->adminUser == '') {
            $this->show->error('The username, password or e-mail address cannot be blank or malformed. Please go back and try again.');
        }

        if (strlen($this->site->adminUser) < $this->vars::USERNAME_MIN_LENGTH || strlen($this->site->adminUser) > $this->vars::USERNAME_MAX_LENGTH) {
            $this->show->error($this->vars->lang['username_length_invalid']);
        }

        if ($iUsername == 'anonymous' || $iUsername == 'xguest123') {
            $this->show->error('The username you provided is not valid for XMB. Please press back and create a different username.');
        }

        if (strlen($this->site->adminPass) < $this->password::MIN_LENGTH) {
            $this->show->error($this->vars->lang['pwtooshort']);
        }

        if (strlen($this->site->adminPass) > $this->password::MAX_LENGTH) {
            $this->show->error($this->vars->lang['pwtoolong']);
        }

        // Duplicates some parts of Core::usernameValidation()
        $nonprinting = '\\x00-\\x1F\\x7F-\\x9F\\xAD';
        $specials = '\\]\'<>\\\\|"[,@';  //Other universal chars disallowed by XMB: []'"<>\|,@
        $sequences = '|  ';  //Phrases disallowed, each separated by '|'
        if ($this->site->adminUser !== preg_replace("#[{$nonprinting}{$specials}]{$sequences}#", '', $this->site->adminUser)) {
            $this->show->error('The username may not contain special characters. Please press back and try again.');
        }

        // these two are used waaaaay down below.
        $vPassword = $this->password->hash($this->site->adminPass);
        $myDate = time();
        $this->show->okay();

        // is XMB already installed?
        $this->show->progress('Checking for previous XMB Installations');
        if ($this->schema->tableExists('settings')) {
            $errStr = 'An existing installation of XMB has been detected.  If you wish to overwrite this installation, please drop your "'
            . $this->vars->tablepre . 'settings" table by using <pre>DROP TABLE `'
            . $this->vars->tablepre . 'settings`;</pre>To install another forum on the same database, go back and enter a different table prefix.';
            $this->show->error($errStr);
        }
        $this->show->okay();

        // Create all tables.
        foreach ($this->schema->listTables() as $table) {
            $this->show->progress("Creating " . $this->vars->tablepre . $table);
            $this->schema->table('overwrite', $table);
            $this->show->okay();
        }


        // -- Insert Data -- //
        // Reminder: Columns without explicit default values must be set on insert for STRICT_ALL_TABLES mode compatibility.
        $this->show->progress("Inserting data into " . $this->vars->tablepre . "restricted");
        $this->db->query(
            "INSERT INTO " . $this->vars->tablepre . "restricted
            (`name`, `case_sensitivity`, `partial`)
            VALUES
            ('Anonymous', '0', '0'),
            ('xguest123', '0', '0')"
        );
        $this->show->okay();

        $this->show->progress("Inserting data into " . $this->vars->tablepre . "forums");
        $this->db->query("INSERT INTO " . $this->vars->tablepre . "forums VALUES ('forum', 1, 'Default Forum', 'on', '', '', 0, 'This is the default forum created during installation<br />To create or modify forums go to the forum section of the administration panel', 'yes', 'yes', '', 0, 0, 0, 0, '31,31,31,63', 'yes', 'on', '');");
        $this->show->okay();

        $this->show->progress("Inserting data into " . $this->vars->tablepre . "ranks");
        $this->db->query(
            "INSERT INTO " . $this->vars->tablepre . "ranks
            VALUES
            ('Newbie',               0, 1, 1, 'yes', ''),
            ('Junior Member',        2, 2, 2, 'yes', ''),
            ('Member',             100, 3, 3, 'yes', ''),
            ('Senior Member',      500, 4, 4, 'yes', ''),
            ('Posting Freak',     1000, 5, 5, 'yes', ''),
            ('Moderator',           -1, 6, 6, 'yes', ''),
            ('Super Moderator',     -1, 7, 7, 'yes', ''),
            ('Administrator',       -1, 8, 8, 'yes', ''),
            ('Super Administrator', -1, 9, 9, 'yes', '')"
        );
        $this->show->okay();

        $this->show->progress("Inserting data into " . $this->vars->tablepre . "settings");
        $this->db->query("INSERT INTO " . $this->vars->tablepre . "settings
            (name, value) VALUES
            ('addtime', '0'),
            ('adminemail', 'webmaster@domain.ext'),
            ('allowrankedit', 'on'),
            ('attachimgpost', 'on'),
            ('attach_remote_images', 'off'),
            ('authorstatus', 'on'),
            ('avastatus', 'on'),
            ('bbinsert', 'on'),
            ('bbname', 'Your Forums'),
            ('bboffreason', ''),
            ('bbrules', 'off'),
            ('bbrulestxt', ''),
            ('bbstatus', 'on'),
            ('captcha_status', 'on'),
            ('captcha_reg_status', 'on'),
            ('captcha_post_status', 'on'),
            ('captcha_search_status', 'off'),
            ('captcha_code_charset', 'A-Z'),
            ('captcha_code_length', '8'),
            ('captcha_code_casesensitive', 'off'),
            ('captcha_code_shadow', 'off'),
            ('captcha_image_type', 'png'),
            ('captcha_image_width', '250'),
            ('captcha_image_height', '50'),
            ('captcha_image_bg', ''),
            ('captcha_image_dots', '0'),
            ('captcha_image_lines', '70'),
            ('captcha_image_fonts', ''),
            ('captcha_image_minfont', '16'),
            ('captcha_image_maxfont', '25'),
            ('captcha_image_color', 'off'),
            ('catsonly', 'off'),
            ('coppa', 'off'),
            ('dateformat', 'dd-mm-yyyy'),
            ('def_tz', '0.00'),
            ('dotfolders', 'on'),
            ('doublee', 'off'),
            ('editedby', 'off'),
            ('emailcheck', 'off'),
            ('faqstatus', 'on'),
            ('filesperpost', '10'),
            ('files_min_disk_size', '9216'),
            ('files_storage_path', ''),
            ('files_subdir_format', '1'),
            ('file_url_format', '1'),
            ('files_virtual_url', ''),
            ('floodctrl', '5'),
            ('footer_options', 'queries-phpsql-loadtimes-totaltime'),
            ('google_captcha', 'off'),
            ('google_captcha_sitekey', ''),
            ('google_captcha_secret', ''),
            ('gzipcompress', 'on'),
            ('hideprivate', 'on'),
            ('hide_banned', 'off'),
            ('hottopic', '20'),
            ('images_https_only', 'off'),
            ('indexshowbar', '2'),
            ('index_stats', 'on'),
            ('ipreg', 'on'),
            ('ip_banning', 'off'),
            ('langfile', 'English'),
            ('mailer_dkim_domain', ''),
            ('mailer_dkim_key_path', ''),
            ('mailer_dkim_selector', ''),
            ('mailer_host', 'default'),
            ('mailer_password', ''),
            ('mailer_port', ''),
            ('mailer_tls', 'auto'),
            ('mailer_type', 'default'),
            ('mailer_username', ''),
            ('maxattachsize', '256000'),
            ('maxdayreg', '25'),
            ('max_avatar_size', '100x100'),
            ('max_image_size', '1000x1000'),
            ('max_thumb_size', '200x200'),
            ('memberperpage', '45'),
            ('memliststatus', 'on'),
            ('notifyonreg', 'off'),
            ('onlinetodaycount', '50'),
            ('onlinetoday_status', 'on'),
            ('postperpage', '25'),
            ('pruneusers', '0'),
            ('quarantine_new_users', 'off'),
            ('quickjump_status', 'on'),
            ('quickreply_status', 'on'),
            ('regoptional', 'off'),
            ('regstatus', 'on'),
            ('regviewonly', 'off'),
            ('reportpost', 'on'),
            ('resetsigs', 'off'),
            ('schema_version', '" . $this->schema::VER . "'),
            ('searchstatus', 'on'),
            ('showsubforums', 'off'),
            ('show_logs_in_threads', 'off'),
            ('sigbbcode', 'on'),
            ('sitename', 'YourDomain.com'),
            ('siteurl', '" . $this->vars->full_url . "'),
            ('smcols', '4'),
            ('smileyinsert', 'on'),
            ('smtotal', '16'),
            ('space_cats', 'off'),
            ('stats', 'on'),
            ('subject_in_title', 'on'),
            ('theme', '1'),
            ('tickercode', 'html'),
            ('tickercontents', '<strong>Welcome to your new XMB Forum!</strong>\nWe recommend changing your forums <a href=\"" . $this->vars->full_url . "admin/settings.php\">settings</a> first.'),
            ('tickerdelay', '4000'),
            ('tickerstatus', 'on'),
            ('timeformat', '12'),
            ('todaysposts', 'on'),
            ('topicperpage', '30'),
            ('u2uquota', '600'),
            ('whosonlinestatus', 'on')"
        );
        $this->show->okay();

        $this->show->progress("Inserting data into " . $this->vars->tablepre . "smilies");
        $this->db->query(
            "INSERT INTO " . $this->vars->tablepre . "smilies
            VALUES
            ('smiley', ':)',             'smile.gif', 1),
            ('smiley', ':(',             'sad.gif', 2),
            ('smiley', ':thumbdown:',    'thumbdown.gif', 3),
            ('smiley', ';)',             'wink.gif', 4),
            ('smiley', ':cool:',         'cool.gif', 5),
            ('smiley', ':mad:',          'mad.gif', 6),
            ('smiley', ':punk:',         'punk.gif', 7),
            ('smiley', ':blush:',        'blush.gif', 8),
            ('smiley', ':love:',         'love.gif', 9),
            ('smiley', ':ninja:',        'ninja.gif', 10),
            ('smiley', ':fake sniffle:', 'fake_sniffle.gif', 11),
            ('smiley', ':smilegrin:',    'smilegrin.gif', 12),
            ('smiley', ':kiss:',         'kiss.gif', 13),
            ('smiley', ':no:',           'no.gif', 14),
            ('smiley', ':post:',         'post.gif', 15),
            ('smiley', ':lol:',          'lol.gif', 16),
            ('smiley', ':sniffle:',      'sniffle.gif', 17),
            ('smiley', ':starhit:',      'starhit.gif', 18),
            ('smiley', ':yes:',          'yes.gif', 19),
            ('smiley', ':grind:',        'grind.gif', 20),
            ('smiley', ':crazy:',        'crazy.gif', 21),
            ('smiley', ':spin:',         'spin.gif', 22),
            ('smiley', ':exclamation:',  'exclamation.gif', 23),
            ('smiley', ':bigsmile:',     'bigsmile.gif', 24),
            ('smiley', ':smirk:',        'smirk.gif', 25),
            ('smiley', ':borg:',         'borg.gif', 26),
            ('smiley', ':rolleyes:',     'rolleyes.gif', 27),
            ('smiley', ':info:',         'info.gif', 28),
            ('smiley', ':question:',     'question.gif', 29),
            ('smiley', ':thumbup:',      'thumbup.gif', 30),
            ('smiley', ':dork:',         'dork.gif', 31),
            ('picon',  '',               'cool.gif', 32),
            ('picon',  '',               'mad.gif', 33),
            ('picon',  '',               'thumbup.gif', 34),
            ('picon',  '',               'thumbdown.gif', 35),
            ('picon',  '',               'post.gif', 36),
            ('picon',  '',               'exclamation.gif', 37),
            ('picon',  '',               'info.gif', 38),
            ('picon',  '',               'question.gif', 39)"
        );
        $this->show->okay();

        $this->show->progress("Inserting data into " . $this->vars->tablepre . "themes");
        $this->db->query("INSERT INTO " . $this->vars->tablepre . "themes (`name`,      `bgcolor`, `altbg1`,  `altbg2`,  `link`,    `bordercolor`, `header`,  `headertext`, `top`,       `catcolor`,   `tabletext`, `text`,    `borderwidth`, `tablewidth`, `tablespace`, `font`,                              `fontsize`, `boardimg`, `imgdir`,       `smdir`,          `cattext`) "
                                           ."VALUES ('XMB Davis', 'bg.gif',  '#FFFFFF', '#f4f7f8', '#24404b', '#86a9b6',     '#d3dfe4', '#24404b',    'topbg.gif', 'catbar.gif', '#000000',   '#000000', '1px',         '97%',        '5px',        'Tahoma, Arial, Helvetica, Verdana', '11px',     'logo.gif', 'images/davis', 'images/smilies', '#163c4b');");
        $this->show->okay();

        $this->show->progress("Inserting data into " . $this->vars->tablepre . "words");
        $this->db->query(
            "INSERT INTO " . $this->vars->tablepre . "words (`find`, `replace1`)
            VALUES
            ('cock',         '[b]****[/b]'),
            ('dick',         '[b]****[/b]'),
            ('fuck',         '[b][Censored][/b]'),
            ('shit',         '[b][Censored][/b]'),
            ('faggot',       '[b][Censored][/b]'),
            ('bitch',        '[b][Censored][/b]'),
            ('whore',        '[b][Censored][/b]'),
            ('mofo',         '[b][Censored][/b]'),
            ('shite',        '[b][Censored][/b]'),
            ('asshole',      '[b][Censored][/b]'),
            ('dumbass',      '[b][Censored][/b]'),
            ('blowjob',      '[b][Censored][/b]'),
            ('porn',         '[b][Censored][/b]'),
            ('masturbate',   '[b][Censored][/b]'),
            ('masturbation', '[b][Censored][/b]'),
            ('jackoff',      '[b][Censored][/b]'),
            ('jack off',     '[b][Censored][/b]'),
            ('s h i t',      '[b][Censored][/b]'),
            ('f u c k',      '[b][Censored][/b]'),
            ('f a g g o t',  '[b][Censored][/b]'),
            ('b i t c h',    '[b][Censored][/b]'),
            ('cunt',         '[b][Censored][/b]'),
            ('c u n t',      '[b][Censored][/b]'),
            ('damn',         'dang')"
        );
        $this->show->okay();

        $this->show->progress("Creating Super Administrator Account");
        $this->sql->addMember([
            'username'   => $this->site->adminUser,
            'password2'  => $vPassword,
            'pwdate'     => $myDate,
            'regdate'    => $myDate,
            'regip'      => $_SERVER['REMOTE_ADDR'] ?? '',
            'email'      => $this->site->adminEmail,
            'status'     => 'Super Administrator',
            'langfile'   => 'English',
            'timeformat' => 12,
            'dateformat' => 'dd-mm-yyyy',
            'mood'       => '',
            'tpp'        => 30,
            'ppp'        => 30,
            'saveogu2u'  => 'yes',
            'emailonu2u' => 'no',
        ]);
        $this->show->okay();

        // Debug mode is enabled by default during install. Try to turn it off so the new forums will look normal.
        if ($this->vars->debug) {
            $this->show->progress("Deactivating debug mode");
            if (is_writable(ROOT . 'config.php')) {
                $configuration = file_get_contents(ROOT . 'config.php');
                $configuration = str_ireplace('$debug = true;', '$debug = false;', $configuration);
                $result = file_put_contents(ROOT . 'config.php', $configuration);
                if (false === $result){
                    $this->show->warning('Please disable debug mode in the config.php file after a successful installation.');
                } else {
                    $this->show->okay();
                }
            } else {
                $this->show->warning('Please disable debug mode in the config.php file after a successful installation.');
            }
        }

        // Safe to remove any temporary files now
    }
}
