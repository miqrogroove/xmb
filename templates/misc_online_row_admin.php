<tr bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow">
<td width="18%"><?= $online['username'] ?></td>
<td align="center" width="10%"><?= $onlinetime ?></td>
<td><?= $online['location'] ?></td>
<td align="center">
<a href="https://whois.domaintools.com/<?= $online['ip'] ?>" onclick="window.open(this.href); return false;" />W</a>
<a href="https://www.net.princeton.edu/cgi-bin/traceroute.pl?target=<?= $online['ip'] ?>" onclick="window.open(this.href); return false;" />T</a>
<a href="https://mxtoolbox.com/SuperTool.aspx?action=ptr%3a<?= $online['ip'] ?>" onclick="window.open(this.href); return false;">L</a>
<a href="https://www.iptrackeronline.com/?ip_address=<?= $online['ip'] ?>" onclick="window.open(this.href); return false;">M</a> <?= $online['ip'] ?></a></td>
</tr>
