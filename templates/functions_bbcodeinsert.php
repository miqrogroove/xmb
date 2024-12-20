<script type="text/javascript"><?= $setbbcodemode ?></script>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['cb_fomatting'] ?><br /><span class="smalltxt">
<input type="radio" name="mode" value="2" onclick="chmode('2')" <?= $mode2check ?> /> <?= $lang['cb_normalmode'] ?><br />
<input type="radio" name="mode" value="0" onclick="chmode('0')" <?= $mode0check ?> /> <?= $lang['cb_advmode'] ?><br />
<input type="radio" name="mode" value="1" onclick="chmode('1')" <?= $mode1check ?> /> <?= $lang['cb_helpmode'] ?></span></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<select name="font" onchange="chfont(this.options[this.selectedIndex].value)" size="1">
<option value="" id="zerofont" selected="selected"><?= $lang['textfont'] ?></option>
<option value="Andale Mono">Andale Mono</option>
<option value="Arial">Arial</option>
<option value="Arial Black">Arial Black</option>
<option value="Book Antiqua">Book Antiqua</option>
<option value="Century Gothic">Century Gothic</option>
<option value="Comic Sans MS">Comic Sans MS</option>
<option value="Courier New">Courier New</option>
<option value="Georgia">Georgia</option>
<option value="Impact">Impact</option>
<option value="Tahoma">Tahoma</option>
<option value="Times New Roman">Times New Roman</option>
<option value="Trebuchet MS">Trebuchet MS</option>
<option value="Script MT Bold">Script MT Bold</option>
<option value="Stencil">Stencil</option>
<option value="Verdana">Verdana</option>
<option value="Lucida Console">Lucida Console</option>
</select>
<select name="size" onchange="chsize(this.options[this.selectedIndex].value)" size="1">
<option value="-2">-2</option>
<option value="-1">-1</option>
<option value="0" id="zerosize" selected="selected">0</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
</select>
<select name="color" onchange="chcolor(this.options[this.selectedIndex].value)" size="1">
<option value="" id="zerocolor" selected="selected"><?= $lang['texttext'] ?></option>
<option value="White" style="color:white;">White</option>
<option value="Black" style="color:black;">Black</option>
<option value="Red" style="color:red;">Red</option>
<option value="Yellow" style="color:yellow;">Yellow</option>
<option value="Pink" style="color:pink;">Pink</option>
<option value="Green" style="color:green;">Green</option>
<option value="Orange" style="color:orange;">Orange</option>
<option value="Purple" style="color:purple;">Purple</option>
<option value="Blue" style="color:blue;">Blue</option>
<option value="Beige" style="color:beige;">Beige</option>
<option value="Brown" style="color:brown;">Brown</option>
<option value="Teal" style="color:teal;">Teal</option>
<option value="Navy" style="color:navy;">Navy</option>
<option value="Maroon" style="color:maroon;">Maroon</option>
<option value="LimeGreen" style="color:limegreen;">LimeGreen</option>
</select>
<?= $spelling_lang ?>
<br />
<a href="javascript:bold()" accesskey="b" title="<?= $lang['cb_insert_bold'] ?>"><img src="<?= $THEME['imgdir'] ?>/bb_bold.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_bold'] ?>" /></a>
<a href="javascript:italicize()" accesskey="i" title="<?= $lang['cb_insert_italics'] ?>"><img src="<?= $THEME['imgdir'] ?>/bb_italicize.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_italics'] ?>" /></a>
<a href="javascript:underline()" accesskey="u" title="<?= $lang['cb_insert_underlined'] ?>"><img src="<?= $THEME['imgdir'] ?>/bb_underline.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_underlined'] ?>" /></a>
<a href="javascript:center()" title="<?= $lang['cb_insert_centered'] ?>" ><img src="<?= $THEME['imgdir'] ?>/bb_center.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_centered'] ?>" /></a>
<a href="javascript:hyperlink()" title="<?= $lang['cb_insert_hyperlink'] ?>" ><img src="<?= $THEME['imgdir'] ?>/bb_url.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_hyperlink'] ?>" /></a>
<a href="javascript:email()" title="<?= $lang['cb_insert_email'] ?>" ><img src="<?= $THEME['imgdir'] ?>/bb_email.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_email'] ?>" /></a>
<a href="javascript:image()" title="<?= $lang['cb_insert_image'] ?>" ><img src="<?= $THEME['imgdir'] ?>/bb_image.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_image'] ?>" /></a>
<a href="javascript:code()" title="<?= $lang['cb_insert_code'] ?>" ><img src="<?= $THEME['imgdir'] ?>/bb_code.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_code'] ?>" /></a>
<a href="javascript:quote()" title="<?= $lang['cb_insert_quote'] ?>" ><img src="<?= $THEME['imgdir'] ?>/bb_quote.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_quote'] ?>" /></a>
<a href="javascript:list()" title="<?= $lang['cb_insert_list'] ?>" ><img src="<?= $THEME['imgdir'] ?>/bb_list.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_list'] ?>" /></a>
<a href="javascript:youtube()" title="<?= $lang['cb_insert_youtube'] ?>"><img src="<?= $THEME['imgdir'] ?>/bb_youtube.gif" border="0" width="23" height="22" alt="<?= $lang['cb_insert_youtube'] ?>" /></a>
</td>
</tr>
