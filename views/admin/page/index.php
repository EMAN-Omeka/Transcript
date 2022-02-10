<?php
head_css();
echo head(array('title' => 'Transcript - Index'));

include(PLUGIN_DIR . '/Transcript/views/admin/page/menu.php');

echo flash();
?>
<script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/tinymce/tinymce-mod.min.js"></script>
<script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/transcript-admin.js"></script>

<a href='<?= WEB_ROOT ?>/admin/transcript/index?refresh=1' id='refresh'>Rafraîchir les index</a>
<br /><br />
<?php
echo $content;

echo foot();
?>

