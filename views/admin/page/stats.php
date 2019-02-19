<?php
head_css();
echo head(array('title' => 'Transcript - Statistiques'));
?>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/transcript'>Tags disponibles</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/transcript/controle'>R&egrave;gles de coh&eacute;rence</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/transcript/list'>Liste des transcriptions</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/transcript/stats'>Statistiques</a>
<?php 
echo flash(); 
echo $content;

$completion = round($nb_transcriptions / $nb_fichiers , 2) * 100;
$nb_fichiers < 2 ? $nb_fichiers = $nb_fichiers . ' fichier' : $nb_fichiers = $nb_fichiers . ' fichiers';
$nb_transcriptions < 2 ? $t = $nb_transcriptions . ' transcription commencée' : $t = $nb_transcriptions . ' transcriptions commencées';

?>

<table>
  <tr><td><?php echo $nb_fichiers . ' dans la base.' ?></td></tr>
  <tr><td><?php echo $t . '.'?></td></tr>
  <tr><td><?php echo 'Base transcrite à ' . $completion . '%' ?></td></tr>
</table>
<?php 
echo foot(); 
?>

