<?php
 echo head(array('title' => 'Page Transcription', 'bodyclass' => 'transcript transcription'));
 echo flash();
?>
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Transcript/css/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/css/mag.css" />
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/theme/default.css" />
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Transcript/css/transcript.css">

<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/tinymce/tinymce-mod.min.js"></script>
<script type="text/javascript" charset="utf-8" src=<?php echo WEB_ROOT . '/plugins/Transcript/javascripts/transcript.js'?> ></script>
<script type="text/javascript" charset="utf-8" src=<?php echo WEB_ROOT . '/plugins/Transcript/javascripts/visualisation.js'?> ></script>
<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/jquery.bridget.js"></script>
<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/js/mag.js"></script>
<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/js/mag-jquery.js"></script>
<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/js/mag-control.js"></script>

<h1>Transcription (<a href='<?php echo $linkToFile ?>'>Voir la notice de ce fichier</a> - Voir le fichier <a href='<?php echo WEB_ROOT; ?>/admin/transcript/exporttei/<?php echo $fileId ?>'>TEI</a>)</h1>

<div id='transcript-zoom'>
  <?php echo $fullSize; ?>
  <div id="transcript-controles" style="text-align:center;padding-top:5px;">
    <button id="transcript-z-moins"><i class="fa fa-search-minus"></i></button>
    <button id="transcript-z-plus"><i class="fa fa-search-plus"></i></button>
    <button id="transcript-z-haut"><i class="fa fa-arrow-up"></i></button>
    <button id="transcript-z-bas"><i class="fa fa-arrow-down"></i></button>
    <button id="transcript-z-gauche"><i class="fa fa-arrow-left"></i></button>
    <button id="transcript-z-droite"><i class="fa fa-arrow-right"></i></button>
    <button id="transcript-reset" title="Afficher l'image entière"><i class="fa fa-square-o"></i></button>
    <button id="transcript-fullscreen" title="Mode plein écran"><i class="fa fa-arrows"></i></button>
  </div>
</div>


<div id="transcript-form">
	<?php echo $form; ?>
</div>

<script>

// Transcript preferences
<?php echo "var transcript_options = ". json_encode($options) . ";\n";?>
<?php echo "var visu = ". json_encode($visu) . ";\n";?>
<?php echo "var ilvl = ". json_encode($ilvl) . ";\n";?>
<?php echo "var clvl = ". json_encode($clvl) . ";\n";?>
<?php echo "var french_names = ". json_encode($french_names) . ";\n";?>
<?php echo "var tag_context = ". $controles . ";\n";?>

var contentDivStyles = $('#content').css(["width", "max-width", "height", "top", "left", "margin", "padding"]);
var fullscreen = false;

$(document).ready(function() {
  var w;
  $("#transcript-reset").click(function () {
    $("#transcript-image").css('width', '100%');
    $("#transcript-image").css('top', '0');
    $("#transcript-image").css('left', '0');
  });
  $("#transcript-z-moins").click(function () {
    w = parseInt($("#transcript-image").width());
    w = parseInt(w / 100) * 90;
    $("#transcript-image").width(w);
  });
  $("#transcript-z-plus").click(function () {
    w = parseInt($("#transcript-image").width());
    w = parseInt(w / 100 * 110);
    $("#transcript-image").width(w);
  });
  $("#transcript-z-gauche").click(function () {
    l = parseInt($("#transcript-image").css('left'));
    w = parseInt($("#transcript-image").width());
    $("#transcript-image").css('left', l - (w / 100 * 10));
  });
  $("#transcript-z-droite").click(function () {
    l = parseInt($("#transcript-image").css('left'));
    w = parseInt($("#transcript-image").width());
    $("#transcript-image").css('left', l + (w / 100 * 10));
  });
  $("#transcript-z-haut").click(function () {
    t = parseInt($("#transcript-image").css('top'));
    h = parseInt($("#transcript-image").height());
    $("#transcript-image").css('top', t - (h / 100 * 10));
  });
  $("#transcript-z-bas").click(function () {
    t = parseInt($("#transcript-image").css('top'));
    h = parseInt($("#transcript-image").height());
    $("#transcript-image").css('top', t + (h / 100 * 10));
  });
  $("#transcript-fullscreen").click(function () {
    l = $(window).width();
    h = $(window).height();

    if (fullscreen == true) {
      $("#content").css(contentDivStyles);
      fullscreen = false;
    } else {
      $('#content').css({
        "width": $(document).width(),
        "max-width": $(document).width(),
        "height": $(document).height(),
        "top": 0,
        "left": 0,
        "margin": 0,
        "padding":0
        }
      );
      fullscreen = true;
    }

  });
});

function resizeIframe(obj) {
  obj.style.height = obj.contentWindow.document.firstChild.scrollHeight + 'px';
}
</script>

<div id="trancript-rendered">
  <iframe id="transcription-full" src='<?php echo WEB_ROOT . '/teibp/transcriptions/' . $xmlFileName ?>' frameborder="0" scrolling="no" onload="resizeIframe(this)" >
  </iframe>
  <div style="clear:both;">
    <ul id='transcript-exports'>
<!--       <li><a href=''>PDF</a></li> -->
      <li><a href='<?php echo WEB_ROOT; ?>/admin/transcript/exporttei/<?php echo $fileId ?>'>Export de la transcription en XML TEI</a></li>
<!--       <li><a href=''>XML</a></li> -->
    </ul>
	<em>Affichage optimis&eacute; pour Firefox. Les expressions MathML seront mal rendues sous les autres navigateurs.</em>
  </div>
</div>

<div id="phpWebRoot" style="display:none;"><?php echo WEB_ROOT; ?></div>
<?php echo $pager ?>
<?php echo $comments ?>
</div>
