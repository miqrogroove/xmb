<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace XMB;

require './header.php';

$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$themeMgr = \XMB\Services\theme();
$vars = \XMB\Services\vars();

$vars->comment_output = false; // If true, CSS would be invalid.
$vars->theme = $sql->getThemeByID(getInt('id'));
if (empty($vars->theme)) {
    header('HTTP/1.0 404 Not Found');
    exit('Not Found');
}
$themeMgr->more_theme_vars();

header("Content-type: text/css");
header("Content-Description: XMB Stylesheet");
header("Cache-Control: public, max-age=604800");
header("Expires: ".gmdate('D, d M Y H:i:s', time() + 604800)." GMT");

$template->addRefs();
$template->process('css.php', echo: true);
