<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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

class Variables
{
    public const FILENAME_MAX_LENGTH = 120; // Size of attachments.filename.
    public const IP_ADDRESS_MAX_LENGTH = 39; // Size of members.regip and posts.useip.
    public const NONCE_AYS_EXP = 300; // Yes/no prompt expiration, in seconds.
    public const NONCE_FORM_EXP = 3600; // Form expiration, in seconds.
    public const NONCE_MAX_AGE = 43200; // CAPTCHA expiration, in seconds.
    public const NONCE_KEY_LEN = 12; // Size of captchaimages.imagestring.
    public const ONLINE_TIMER = 600; // Visitors are offline after this many seconds.
    public const PAGING_MAX = 100; // $this->tpp and $this->ppp may not be more than this value.
    public const PAGING_MIN = 5; // $this->tpp and $this->ppp may not be less than this value.
    public const POST_MSG_MAX_LEN = 65535; // Size of posts.message.
    public const THREAD_SUB_MAX_LEN = 128; // Size of threads.subject.
    public const USERNAME_MIN_LENGTH = 3; // New usernames may not be shorter than this.
    public const USERNAME_MAX_LENGTH = 32; // Size of members.username.
    // permissions constants
    public const PERMS_COUNT = 4; // Number of raw bit sets stored in postperm setting.
    // indexes used in permissions arrays
    public const PERMS_RAWPOLL = 0;
    public const PERMS_RAWTHREAD = 1;
    public const PERMS_RAWREPLY = 2;
    public const PERMS_RAWVIEW = 3;
    public const PERMS_POLL = 40;
    public const PERMS_THREAD = 41;
    public const PERMS_REPLY = 42;
    public const PERMS_VIEW = 43; // View is now = Rawview || Userlist
    public const PERMS_USERLIST = 44;
    public const PERMS_PASSWORD = 45;

    public const cheHTML = 'checked="checked"';
    public const selHTML = 'selected="selected"';

    // status string to bit field assignments
    public array $status_enum = [
        'Super Administrator' => 1,
        'Administrator'       => 2,
        'Super Moderator'     => 4,
        'Moderator'           => 8,
        'Member'              => 16,
        'Guest'               => 32,
        ''                    => 32,
        'Reserved-Future-Use' => 64,
        'Banned'              => (1 << 30),
    ]; //$status['Banned'] == 2^30

    // status bit to $lang key assignments
    public array $status_translate = [
        1         => 'superadmin',
        2         => 'textadmin',
        4         => 'textsupermod',
        8         => 'textmod',
        16        => 'textmem',
        32        => 'textguest1',
        (1 << 30) => 'textbanned',
    ];

    public array $lang = [];
    public array $mailer = [];
    public array $plugadmin = [];
    public array $plugimg = [];
    public array $plugname = [];
    public array $plugurl = [];
    public array $self = [];
    public array $settings = [];
    public array $theme = [];

    public string $charset = '';
    public string $cookiedomain = '';
    public string $cookiepath = '';
    public string $dateformat = '';
    public string $dbname = '';
    public string $dbuser = '';
    public string $dbpw = '';
    public string $dbhost = '';
    public string $database = '';
    public string $full_url = '';
    public string $langfile = '';
    public string $oldtopics = '';
    public string $onlineip = '';
    public string $tablepre = '';
    public string $timecode = '';
    public string $timeoffset = '';
    public string $url = '';
    public string $versiongeneral = '';
    public string $versionshort = '';
    public string $xmbuser = '';

    public bool $allow_spec_q = false;
    public bool $comment_output = false;
    public bool $cookiesecure = false;
    public bool $debug = false;
    public bool $log_mysql_errors = false;
    public bool $pconnect = false;
    public bool $show_full_info = true;
    
    public int $lastvisit = 0;
    public int $onlinetime = 0;
    public int $ppp = 0;
    public int $tpp = 0;
    
    public float $starttime = 0;
}
