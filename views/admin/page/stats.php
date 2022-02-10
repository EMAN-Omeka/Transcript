<?php
head_css();
echo head(array('title' => 'Transcript - Statistiques'));

echo flash();

include(PLUGIN_DIR . '/Transcript/views/admin/page/menu.php');

echo $content;

?>

<table>
<!--   <tr><td><?php echo $nb_fichiers . ' dans la base.' ?></td></tr> -->
  <tr><td><?php echo $nb_fichiers_en_cours . ' fichier(s) sur ' . $nb_fichiers .' en cours de transcription (' . $percent_files . '% du corpus).' ?></td></tr>
  <tr><td><?php echo $nb_items_en_cours . ' item(s) sur ' . $nb_items .' avec au moins un fichier en cours de transcription (' . $percent . '% du corpus).' ?></td></tr>
<!--   <tr><td><?php echo 'Base transcrite à ' . $percent . '%' ?> (nombre de fichiers mentionnés dans une transcription par rapport au nombre total de fichiers).</td></tr> -->
</table>
<?php
echo foot();
?>

