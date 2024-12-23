<tr class="tablerow">
 <td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachment'] ?></td>
 <td bgcolor="<?= $THEME['altbg2'] ?>" id="uploads">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?= $SETTINGS['maxattachsize'] ?>" />
  <input type="file" name="attach1" size="20" /><?= $attachlimits ?>
 </td>
</tr>
<tr class="tablerow" id="multiattachrow" style="display: none;">
 <td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmentm'] ?></td>
 <td bgcolor="<?= $THEME['altbg2'] ?>">
  <a href="javascript:multiAttach()" id="multiattachlink"><?= $lang['attachmore'] ?></a>
 </td>
</tr>
<script type="text/javascript">
 <!--//--><![CDATA[//><!--
  var uploadBoxCount = 1;
  var uploadBoxMax = <?= $maxuploads ?>;
  var uploadTotalLimits = '<?= $lang['attachmaxtotal'] ?>';

  if (uploadBoxMax > 1) {
    document.getElementById('multiattachrow').removeAttribute('style');
  }

  function multiAttach() {
    var newnode = document.createElement('span');
    if (uploadBoxCount < uploadBoxMax) {
      uploadBoxCount++;
      newnode.innerHTML = '<br /><input type="file" name="attach' + uploadBoxCount + '" size="20" />';
      document.getElementById('uploads').appendChild(newnode);
      if (uploadBoxCount == 2 && uploadTotalLimits != '') {
        document.getElementById('multiattachlink').parentNode.innerHTML += '<br />' + uploadTotalLimits;
      }
    }
    if (uploadBoxCount >= uploadBoxMax) {
      noMoreMultiAttach();
    }
  }

  function noMoreMultiAttach() {
    var multiAttachRow;
    if (uploadBoxCount == 1) {
      multiAttachRow = document.getElementById('multiattachrow');
      multiAttachRow.parentNode.removeChild(multiAttachRow);
    } else {
      document.getElementById('multiattachlink').parentNode.innerHTML = uploadTotalLimits;
    }
  }
 //--><!]]>
</script>
