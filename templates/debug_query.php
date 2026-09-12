<table style="width: 97%;"><colgroup span="3" /><tr><td style="width: 2em;">#</td><td style="width: 8em;">Duration:</td><td>Query:</td></tr>
<?php
    foreach ($stuff as $row) {
        echo " <tr><td><strong>{$row['number']}.</strong></td><td>{$row['time']}</td><td>{$row['val']}</td></tr>";
    }
?>
</table>
