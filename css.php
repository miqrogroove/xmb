<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
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
 *
 **/

define('X_SCRIPT', 'css.php');

require 'header.php';

loadtemplates( 'css' );

$THEME = \XMB\SQL\getThemeByID( getInt( 'id' ) );
if ( empty( $THEME ) ) {
    header('HTTP/1.0 404 Not Found');
    exit('Not Found');
}
more_theme_vars();
extract( $THEME );

$comment_output = false; // If true, CSS will be invalid.
eval('$css = "'.template('css').'";');

header("Content-type: text/css");
header("Content-Description: XMB Stylesheet");
header("Cache-Control: public, max-age=604800");
header("Expires: ".gmdate('D, d M Y H:i:s', time() + 604800)." GMT");

echo $css;

return;