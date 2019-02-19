<?php
head_css();
echo head(array('title' => 'Transcript - Liste des transcriptions'));
?>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/transcript'>Tags disponibles</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/transcript/controle'>R&egrave;gles de coh&eacute;rence</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/transcript/list'>Liste des transcriptions</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/transcript/stats'>Statistiques</a>
<?php 
echo flash(); 

echo $content;
?>
<?php 
echo foot(); 
?>

