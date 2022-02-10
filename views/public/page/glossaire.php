<?php
head_css();
echo head(array('title' => 'Terme - ', 'bodyclass' => 'transcript list-terms'));
?>
<link rel="stylesheet" href="<?= WEB_ROOT ?>/plugins/Transcript/css/transcript.css">
<h2>Index des termes</h2>

<?php
echo $content;

echo foot(); ?>

