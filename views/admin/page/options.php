<?php
head_css();
echo head(array('title' => 'Transcript - Options'));

include(PLUGIN_DIR . '/Transcript/views/admin/page/menu.php');

echo flash();

echo $content;

echo foot();
?>

