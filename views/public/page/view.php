<?php echo head(array('title' => 'Transcript - Transcription plein écran', 'bodyclass' => 'transcript transcription transcription-view')); ?>

<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Transcript/css/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/css/mag.css" />
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/theme/default.css" />
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Transcript/css/transcript.css">
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
>
<script type="text/javascript" charset="utf-8" src=<?php echo WEB_ROOT ?>/plugins/Transcript/javascripts/transcript.js ></script> 
<script type="text/javascript" charset="utf-8" src=<?php echo WEB_ROOT ?>/plugins/Transcript/javascripts/visualisation.js ></script> 
<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/jquery.bridget.js"></script>
<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/js/mag.js"></script>
<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/js/mag-jquery.js"></script>
<script src="<?php echo WEB_ROOT; ?>/plugins/Transcript/javascripts/magnificent/src/js/mag-control.js"></script>

<script>
// Transcript preferences

<?php echo "var transcript_options = ". json_encode($options) . ";\n";?> 
<?php echo "var visu = ". json_encode($visu) . ";\n";?> 
<?php echo "var ilvl = ". json_encode($ilvl) . ";\n";?> 
<?php echo "var clvl = ". json_encode($clvl) . ";\n";?> 
<?php echo "var french_names = ". json_encode($french_names) . ";\n";?> 
<?php echo "var tag_context = ". $controles . ";\n";?>   
</script>
<script>
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
</script>
<style>
#overlay {
	width:99.9%;
 	position:absolute; 
/* 	min-height:2000px; */
	top:40px;
	left:0;
	background:#eee;
	z-index:10000000;
	border:#111 1px solid;
}
#overlay-close {
	position:absolute;
	right:10px;
	bottom:10px;
}
#left, #right {
	position:relative;
	width:49.5%;
	border:#222 1px solid;
	top:10px;
	overflow:visible;
	display:block;
	margin-bottom:50px;
}
#left {
	margin-left:5px;	
	clear:left;
	float:left;
}
#right {
	right:5px;
	width:49%;
	clear:both;
	clear:right;
	float:right;
}
#left img {
	width:100%;
}
iframe#transcription-full {
	width:100%;
	clear:both;
	overflow:visible;
}
footer {
  clear:both;
}
</style>

<div id="overlay">
  <span style='clear:both;display:block;'>Transcription (<a href='<?php echo WEB_ROOT . "/files/show/" . $fileId; ?>'>Voir la notice de ce fichier</a>)</span>
  
<!--	<div id="left"><img src="<?php echo WEB_ROOT ?>/files/original/<?php echo $filename; ?>" /></div> -->
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
<!--    <button id="transcript-fullscreen" title="Mode plein écran"><i class="fa fa-arrows"></i></button>     -->
  </div> 
</div>
	<div id="right"><iframe id="transcription-full" src='<?php echo WEB_ROOT . '/teibp/transcriptions/' . $xmlFileName ?>' frameborder="0" scrolling="no" onload="resizeIframe(this)" ></iframe></div>
	<div style="position:relative;margin-left:20%;clear:both;">
		<h3><em>Affichage optimisé pour Firefox. Les expressions MathML seront mal rendues sous les autres navigateurs.</em></h3>
<?php echo foot(); ?>	  		
	</div>	

</div>
<script>
  function resizeIframe(obj) {
  obj.style.height = obj.contentWindow.document.firstChild.scrollHeight + 'px';
}
</script>
<div id="phpWebRoot" style="display:none;"><?php echo WEB_ROOT; ?></div>
