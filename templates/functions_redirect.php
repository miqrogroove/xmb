<script type="text/javascript">
function redirect() {
    window.location.replace("<?= $path ?>");
}
setTimeout("redirect();", <?= $timeout ?>);
</script>
