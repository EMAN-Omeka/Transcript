<?php
echo head(array('title' => 'Browse transcriptions', 'bodyclass' => 'transcript browse'));
echo flash();
if ($content) :
  echo $content;
else :
?>

  <link rel="stylesheet" href="https://eman-archives.org/exist/apps/tei-publisher/transform/EMAN.css">

  <script type="text/javascript" charset="utf-8" src=<?= WEB_ROOT . '/plugins/Transcript/javascripts/functions.js'?> ></script>
  <?php if (current_user()) : ?>
    <script type="text/javascript" charset="utf-8" src=<?= WEB_ROOT . '/plugins/Transcript/javascripts/transcript.js'?> ></script>
  <?php else : ?>
    <script type="text/javascript" charset="utf-8" src=<?= WEB_ROOT . '/plugins/Transcript/javascripts/transcript-public.js'?> ></script>
  <?php endif; ?>

  <!-- TEI Publisher -->
  <script src="https://unpkg.com/@webcomponents/webcomponentsjs@2.4.3/webcomponents-loader.js"></script>
  <script type="module" src="https://unpkg.com/@teipublisher/pb-components@latest/dist/pb-components-bundle.js"></script>

  <link rel="stylesheet" href="<?= WEB_ROOT ?>/plugins/Transcript/css/transcript.css">

  <div id='toolbar'><?=$toolbar ?><div id='import-output'></div></div>

  <?php if (current_user()) : ?>

    <!-- Codemirror -->
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/lib/codemirror.js"></script>

    <link rel="stylesheet" href="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/hint/show-hint.css">
    <link rel="stylesheet" href="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/fold/foldgutter.css" />
    <link rel="stylesheet" href="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/dialog/dialog.css">

    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/fold/foldcode.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/fold/foldgutter.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/fold/brace-fold.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/fold/xml-fold.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/fold/indent-fold.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/fold/comment-fold.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/edit/matchtags.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/edit/closebrackets.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/edit/closetag.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/display/autorefresh.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/selection/mark-selection.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/search/match-highlighter.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/mode/overlay.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/dialog/dialog.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/search/searchcursor.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/search/search.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/scroll/annotatescrollbar.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/search/matchesonscrollbar.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/search/jump-to-line.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/hint/show-hint.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/hint/xml-hint.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/mode/xml/xml.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/xml4tei/js/xml4tei.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/xml4tei/js/xml4teiSchema2json.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/xml4tei/js/xml4teiNte.js"></script>
    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/codemirror/addon/xml4tei/js/xml4teiHelp.js"></script>

    <script src="<?= WEB_ROOT ?>/plugins/Transcript/javascripts/tinymce/tinymce-mod.min.js"></script>

    <script>
    $ = jQuery;
    // Transcript preferences
    <?php echo "var tag_context = ". $controles . ";\n";?>

    var contentDivStyles = $('#content').css(["width", "max-width", "height", "top", "left", "margin", "padding"]);
    var fullscreen = false;
    </script>

    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>

    <div id='transcript-wrapper'>

    <div id='file-info'></div>
    <div id='messages'></div>
    <div id='termes-info'></div>

      <div id='transcript-zoom'>
        <div id='transcript-image-container'>
        </div>
      </div>

      <div id="transcript-form">
        <button id="transcript-show-source">Source</button>
        <button id="transcript-validate">Valider</button>
        <div class="buttons-nav"></div>
        <div id='currenttag'></div>
        <div id='code-mirror-wrapper'>
          <div id="validation-response"></div>
          <textarea id='codemirror-edit' class='tei-editor' data-xmlschema="<?= WEB_ROOT ?>/plugins/Transcript/resources/cm-tei-schema.xml"></textarea>
        </div>
      	<?php echo $form; ?>
      </div>

    </div>
  <?php endif; ?>

  <div id='transcript-rendition'>
    <?php if (! current_user()) : ?>
      <div id='file-info'></div>
      <div id='termes-info'></div>
      <div id="trancript-pictures"><img src='<?= WEB_ROOT ?>/plugins/Transcript/resources/no-image.jpg' /></div>
      <div id="trancript-rendered"></div>
    <?php endif; ?>
  </div>
<!--   <button id='refresh-rendition'>Rafraîchir</button> -->


  <div id="phpWebRoot" style="display:none;"><?php echo WEB_ROOT; ?></div>
  <div id="userRole" style="display:none;"><?php echo $userRole; ?></div>
  <?php // echo $pager ?>
  <?php echo $comments ?>
  </div>
<?php endif; ?>

<?php echo foot(); ?>