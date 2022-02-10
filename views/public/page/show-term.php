<?php
head_css();
echo head(array('title' => 'Terme - ', 'bodyclass' => 'transcript term'));
?>
<link rel="stylesheet" href="<?= WEB_ROOT ?>/plugins/Transcript/css/transcript.css">
<h2><?= $term?></h2>
<p><?= $definition ?></p>
<br /><br />
<h3>Informations : </h3>
<?= $fieldsvalues ?>
<h3>Apparaît dans : </h3>
<p><?= $occurrences ?></p>
<h3>Termes liés : </h3>
<p><?= $linkedTerms ?></p>
<a href="<?= WEB_ROOT ?>/transcript/glossaire">Aller au glossaire</a>
<?php echo foot(); ?>

