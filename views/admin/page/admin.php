<?php

head_css();

echo head(array('title' => 'Transcript - Tags Disponibles'));

echo flash();

include(PLUGIN_DIR . '/Transcript/views/admin/page/menu.php');

?>

<div style='clear:both;margin-bottom:30px;'>
  <label>Activer tous les tags</label>
  <input id='checkall-act' type='checkbox' />
  <label>Activer tous les commentaires</label>
  <input id='checkall-balises' type='checkbox' />
  <label>Activer tous les icones</label>
  <input id='checkall-icones' type='checkbox' />
<!--  <a id="transcript-add-tag" class='add button small green'>Ajouter un tag</a> -->
</div>

<script>
window.jQuery = window.$ = jQuery;

jQuery(document).ready(function() {
	$('#checkall-act').change(function() {
		var state = $(this).prop('checked');
		$("#TranscriptAdminForm input[value='act']").each(function (i, el) {
			$(el).prop('checked', state);
		});
	});
	$('#checkall-balises').change(function() {
		var state = $(this).prop('checked');
		$("#TranscriptAdminForm input[value='balises']").each(function (i, el) {
			$(el).prop('checked', state);
		});
	});
	$('#checkall-icones').change(function() {
		var state = $(this).prop('checked');
		$("#TranscriptAdminForm input[value='icones']").each(function (i, el) {
			$(el).prop('checked', state);
		});
	});
	$('#transcript-reset-options').click(function() {
    if (confirm('Vous êtes sur le point de perdre tous vos réglages et de revenir aux options par défaut. Êtes-vous sûr de vouloir continuer ?')) {
        window.location = '<?php echo WEB_ROOT; ?>/admin/transcript/reset';
    } else {
        return false;
    }
	});
	$('#transcript-export-options').click(function() {
     window.open('<?php echo WEB_ROOT; ?>/admin/transcript/export', '_blank');
	});
	$('#transcript-import-options').click(function() {
     window.open('<?php echo WEB_ROOT; ?>/admin/transcript/import');
	});
	$('#transcript-add-tag').click(function() {
    var tag = prompt('Nom du tag à ajouter', '');
    tag = tag.replace(/\d+/g, '').replace(/[^a-z0-9\s]/gi, '');
    if (prompt != '') {
       window.location = '<?php echo WEB_ROOT; ?>/admin/transcript?addtag=' + tag;
    }
  });
	$('#submit2').click(function() {
    if (confirm("Vous êtes sur le point de perdre tous vos réglages et d'en importer de nouveaux. Êtes-vous sûr de vouloir continuer ?")) {
      return true;
    } else {
      return false;
    }
 	});

});
</script>

<?php echo $form; ?>
<a id="transcript-reset-options" class='add button big red'>Revenir aux options par défaut</a>&nbsp;
<a id="transcript-export-options" class='add button big green'>Archiver les options courantes</a>
<div id="transcript-optionsfile" style="clear:both;">
<?php echo $formfichier; ?>
</div>

<?php echo foot(); ?>

