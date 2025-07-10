<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
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

class URL2Text
{
    private array $fname = []; // Permitted forum name cache.
    private array $tsub = []; // Permitted thread subject cache.
    private string $restrict = ''; // Permitted FID query condition cache.

    public function __construct(
        private Core $core,
        private DBStuff $db,
        private Forums $forums,
        private SmileAndCensor $smile,
        private Variables $vars,
    ) {
        // Property promotion.
    }

    public function convert(string $url): array
    {
        $lang = &$this->vars->lang;

        if ($this->restrict == '') {
            $fids = implode(',', $this->core->permittedFIDsForThreadView());
            if (strlen($fids) == 0) {
                $this->restrict = ' FALSE';
            } else {
                $this->restrict = " f.fid IN ($fids)";
            }
        }

        if (false !== strpos($url, '/viewthread.php')) {
            $temp = explode('?', $url);
            if (count($temp) > 1) {
                $tid = 0;
                if (! empty($temp[1])) {
                    $urls = explode('&', $temp[1]);
                    foreach ($urls as $key => $val) {
                        if (strpos($val, 'tid') !== false) {
                            $tid = (int) substr($val, 4);
                        }
                    }
                }

                $location = $lang['onlinenothread'];
                if (isset($this->tsub[$tid])) {
                    $location = $lang['onlineviewthread'].' '.$this->tsub[$tid];
                } else {
                    $query = $this->db->query("SELECT t.fid, t.subject FROM " . $this->vars->tablepre . "forums f INNER JOIN " . $this->vars->tablepre . "threads t USING (fid) WHERE " . $this->restrict . " AND t.tid = $tid");
                    while ($locate = $this->db->fetch_array($query)) {
                        $location = $lang['onlineviewthread'] . ' ' . $this->core->rawHTMLsubject(stripslashes($locate['subject']));
                        $this->tsub[$tid] = $locate['subject'];
                    }
                    $this->db->free_result($query);
                }
            } else {
                $location = $lang['onlinenothread'];
            }
        } elseif (false !== strpos($url, '/forumdisplay.php')) {
            $temp = explode('?', $url);
            if (count($temp) > 1) {
                $fid = 0;
                $urls = explode('&', $temp[1]);
                if (! empty($temp[1])) {
                    foreach ($urls as $key => $val) {
                        if (strpos($val, 'fid') !== false) {
                            $fid = (int) substr($val, 4);
                        }
                    }
                }

                $location = $lang['onlinenoforum'];
                if (isset($this->fname[$fid])) {
                    $location = "{$lang['onlineforumdisplay']} " . $this->fname[$fid];
                } else {
                    $locate = $this->forums->getForum($fid);
                    if (null !== $locate) {
                        $perms = $this->core->checkForumPermissions($locate);
                        if ($this->vars->settings['hideprivate'] == 'off' || $locate['type'] == 'group' || $perms[$this->vars::PERMS_VIEW]) {
                            $this->fname[$fid] = fnameOut($locate['name']);
                            $location = "{$lang['onlineforumdisplay']} " . $this->fname[$fid];
                        }
                    }
                }
            } else {
                $location = $lang['onlinenoforum'];
            }
        } elseif (false !== strpos($url, "/memcp.php")) {
            if (false !== strpos($url, 'action=profile')) {
                $location = $lang['onlinememcppro'];
            } elseif (false !== strpos($url, 'action=subscriptions')) {
                $location = $lang['onlinememcpsub'];
            } elseif (false !== strpos($url, 'action=favorites')) {
                $location = $lang['onlinememcpfav'];
            } else {
                $location = $lang['onlinememcp'];
            }
        } elseif (false !== strpos($url, '/admin/') || false !== strpos($url, '/cp2.php')) {
            $location = $lang['onlinecp'];
            if (! X_ADMIN) {
                $url = 'index.php';
            }
        } elseif (false !== strpos($url, '/editprofile.php')) {
            $location = $lang['onlinecp'];
            if (! X_SADMIN) {
                $url = 'index.php';
            }
        } elseif (false !== strpos($url, '/faq.php')) {
            $location = $lang['onlinefaq'];
        } elseif (false !== strpos($url, '/index.php')) {
            if (false !== strpos($url, 'gid=')) {
                $temp = explode('?', $url);
                $gid = (int) str_replace('gid=', '', $temp[1]);
                $cat = $this->forums->getForum($gid);
                if ($cat === null) {
                    $location = $lang['onlinecatunknown'];
                } elseif ($cat['type'] != 'group') {
                    $location = $lang['onlinecatunknown'];
                } else {
                    $location = $lang['onlineviewcat'].fnameOut($cat['name']);
                }
            } else {
                $location = $lang['onlineindex'];
            }
        } elseif (false !== strpos($url, '/lost.php')) {
            $location = $lang['onlinelostpw'];
            if (! X_SADMIN) {
                $url = 'lost.php';
            }
        } elseif (false !== strpos($url, '/member.php')) {
            if (false !== strpos($url, 'action=reg')) {
                $location = $lang['onlinereg'];
            } elseif (false !== strpos($url, 'action=viewpro')) {
                $location = $lang['onlinenoprofile']; // initialize
                $temp = explode('?', $url);
                $urls = explode('&', $temp[1]);
                if (isset($urls[1]) && !empty($urls[1]) && $urls[1] != 'member=') {
                    foreach ($urls as $argument) {
                        if (strpos($argument, 'member=') !== false) {
                            $member = str_replace('member=', '', $argument);
                            $member = rawurldecode(str_replace('+', ' ', $member));
                            $member = preg_replace('#[\]\'\x00-\x1F\x7F<>\\\\|"[,@]#', '', $member);
                            // TODO: This needs to be validated or removed rather than censored.
                            $member = htmlEsc($this->smile->censor($member));
                            $location = str_replace('$member', $member, $lang['onlineviewpro']);
                            break;
                        }
                    }
                }
            } else {
                $location = $lang['onlineunknown'];
            }
        } elseif (false !== strpos($url, 'misc.php')) {
            if (false !== strpos($url, 'login')) {
                $location = $lang['onlinelogin'];
            } elseif (false !== strpos($url, 'logout')) {
                $location = $lang['onlinelogout'];
            } elseif (false !== strpos($url, 'lostpw')) {
                $location = $lang['onlinelostpw'];
            } elseif (false !== strpos($url, 'online')) {
                $location = $lang['onlinewhosonline'];
            } elseif (false !== strpos($url, 'onlinetoday')) {
                $location = $lang['onlineonlinetoday'];
            } elseif (false !== strpos($url, 'list')) {
                $location = $lang['onlinememlist'];
            } elseif (false !== strpos($url, 'captchaimage')) {
                $location = $lang['onlinereg'];
            } else {
                $location = $lang['onlineunknown'];
            }
        } elseif (false !== strpos($url, '/post.php')) {
            if (false !== strpos($url, 'action=edit')) {
                $location = $lang['onlinepostedit'];
            } elseif (false !== strpos($url, 'action=newthread')) {
                $location = $lang['onlinepostnewthread'];
            } elseif (false !== strpos($url, 'action=reply')) {
                $location = $lang['onlinepostreply'];
            } else {
                $location = $lang['onlineunknown'];
            }
        } elseif (false !== strpos($url, '/quarantine.php')) {
            $location = $lang['onlinetopicadmin'];
        } elseif (false !== strpos($url, '/search.php')) {
            $location = $lang['onlinesearch'];
        } elseif (false !== strpos($url, '/stats.php')) {
            $location = $lang['onlinestats'];
        } elseif (false !== strpos($url, '/today.php')) {
            $location = $lang['onlinetodaysposts'];
        } elseif (false !== strpos($url, '/tools.php')) {
            $location = $lang['onlinetools'];
        } elseif (false !== strpos($url, '/topicadmin.php')) {
            $location = $lang['onlinetopicadmin'];
        } elseif (false !== strpos($url, '/u2u.php')) {
            if (false !== strpos($url, 'action=send')) {
                $location = $lang['onlineu2usend'];
            } elseif (false !== strpos($url, 'action=delete')) {
                $location = $lang['onlineu2udelete'];
            } elseif (false !== strpos($url, 'action=ignore') || false !== strpos($url, 'action=ignoresubmit')) {
                $location = $lang['onlineu2uignore'];
            } elseif (false !== strpos($url, 'action=view')) {
                $location = $lang['onlineu2uview'];
            } elseif (false !== strpos($url, 'action=folders') || false !== strpos($url, 'folder=')) {
                $location = $lang['onlinemanagefolders'];
            } else {
                $location = $lang['onlineu2uint'];
            }

            if (! X_SADMIN) {
                $url = '/u2u.php';
            }
        } elseif (false !== strpos($url, '/vtmisc.php')) {
            if (false !== strpos($url, 'action=report')) {
                $location = $lang['onlinereport'];
            } elseif (false !== strpos($url, 'action=votepoll')) {
                $location = $lang['onlinevote'];
            } else {
                $location = $lang['onlineunknown'];
            }
        } elseif (false !== strpos($url, '/buddy.php')) {
            if (false !== strpos($url, 'action=add2u2u')) {
                $location = $lang['onlinebuddyadd2u2u'];
            } elseif (false !== strpos($url, 'action=add')) {
                $location = $lang['onlinebuddyadd'];
            } elseif (false !== strpos($url, 'action=edit')) {
                $location = $lang['onlinebuddyedit'];
            } elseif (false !== strpos($url, 'action=delete')) {
                $location = $lang['onlinebuddydelete'];
            } else {
                $location = $lang['onlinebuddy'];
            }
        } else {
            $location = $lang['onlineindex'];
        }

        $return = [
            'url' => attrOut($this->core->makeFullURL($url)),
            'text' => $location,
        ];
        return $return;
    }
}
