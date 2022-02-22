window.jQuery = window.$ = $ = jQuery;

boutons = [];
var buttons;

$(window).on('load', function() {

  WEB_ROOT = $('#phpWebRoot').html();
  var textarea = document.getElementById('codemirror-edit');
  var xmlschema = $(textarea).attr('data-xmlschema');

  var urlhash = 'span' + window.location.hash;

  // TODO : Voir pour XML Lint (warnings dans la marge pendant la saisie)
  var cmEditor = CodeMirror.fromTextArea(
    textarea, {
      mode: 'xml',
      lineWrapping: true,
      foldGutter: true,
      showCursorWhenSelecting: true,
      styleActiveLine: true,
      lineNumbers: true,
      matchTags: {
        bothTags: true
      },
      autoCloseBrackets: true,
      autoCloseTags: {
        whenClosing: true,
        whenOpening: false,
        emptyTags: 'lb,pb,cb,handShift,milestone,ptr',
      },
      gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
      extraKeys: {
        "'>'": completeAfter,
        "'/'": completeIfAfterLt,
        "' '": completeIfInTag,
        "'='": completeIfInTag,
        "Ctrl-Space": "autocomplete",
        "Ctrl-Q": function(cm) {
          cm.foldCode(cm.getCursor());
        },
        "Ctrl-M": "toMatchingTag"
      },
      hintOptions: {
        schemaInfo: getCmJson(xmlschema),
      }
    }
  );
  $.ajax({
    type: "GET",
    url: $('#phpWebRoot').html() + '/plugins/Transcript/resources/cm-tei-schema.xml',
    dataType: "xml",
    async: false,
    success: function(xml) {
      $(xml).find(':root > *').each(
        function(i, elem) {
          name = elem.nodeName;
          ihmName = $(elem).find('ihmName').text();
          menuName = $(elem).find('menuName').text();
          children = [];
          $(elem).find('children').each(function(i, child) {
            childName = $(child).html();
            children.push(childName.toLowerCase());
          });
          attr = [];
          $.each(elem.attributes, function(index, attribute) {
            if (attribute.name != '') {
              valeurs = [];
              valeurs.push({
                texte: attribute.value,
                valeur: attribute.value
              });
              attr.push({
                att: attribute.name,
                values: valeurs
              });
            }
          });
          boutons.push({
            cl: name,
            att: attr,
            ti: ihmName,
            menu: menuName
          });
        });
    },
    error: function(xhr, ajaxOptions, thrownError) {
      console.log(xhr.status);
      console.log(thrownError);
    }
  });

  tinymce.init({
    selector: '#transcription',
    language: 'fr_FR',
    height: 500,
    theme: 'modern',
    menubar: "tools teistructure teimef teiannot teiselfclosing",
    element_format: 'xhtml',
    entities: '160,nbsp,162,cent,8364,euro,163,pound,Sum',
    menu: {
      teistructure: {
        title: 'Structure',
        items: fill_menu('struct')
      },
      teimef: {
        title: 'Apparence',
        items: fill_menu('mef')
      },
      teiannot: {
        title: 'Annoter',
        items: fill_menu('annot')
      },
      teiselfclosing: {
        title: 'Tags vides',
        items: fill_menu('empty')
      },
    },
    schema: 'html5',
    extended_valid_elements: add_valid_elements(), // Liste des balises TEI
    invalid_elements: null,
    short_ended_elements: 'lb,pb,cb,handshift,milestone,ptr',
    verify_html: false,
    protect: [
						/<math.*?>([\s\S]*?)<\/math>/g
          ],
    forced_root_block: false,
    entity_encoding: 'raw',
    init_instance_callback: function(editor) {
      editor.on('NodeChange', function(e) {
        $('.mce-menu-item-normal').removeClass('tei-ok tei-ko');
        $('.mce-menu-item-normal').show();
        ptr = $(e.element).closest('ptr', tinymce.activeEditor.$('body'));
        if (ptr.length > 0 && typeof $('#transcript-image-anchor-' + $(ptr).attr('target')) != "undefined") {
          $('#transcript-image-container').scrollTop($('#transcript-image-anchor-' + $(ptr).attr('target')).offset().top - $('#transcript-image-container').offset().top + +$('#transcript-image-container').scrollTop() - 20, 'slow');
        } else {
          $('#transcript-image-container').scrollTop(0, 'slow');
        }
      });
      $('#transcript-show-source').show();
      editor.setContent(removeElements(editor.getContent(), 'i'));
    },
    plugins: [
    'advlist autolink lists link image charmap print preview hr anchor pagebreak',
    'searchreplace wordcount visualblocks visualchars fullscreen',
    'insertdatetime media nonbreaking save contextmenu directionality',
    'emoticons template paste textcolor colorpicker textpattern imagetools codesample'
  ],
    toolbar1: 'undo redo | insert | gras italique souligne | pleft pcenter pright pjustify | deletePTR',
    toolbar2: fill_toolbar(),
    image_advtab: true,
    contextmenu: "link image inserttable",
    content_css: [
    '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
    '//www.tiny.cloud/css/codepen.min.css',
    $('#phpWebRoot').html() + '/plugins/Transcript/css/eman.css',
  ],
    setup: function(editor) {
      function manageButtons() {
        var btn = this;
        editor.on('NodeChange', function(e) {
          element = get_real_parent(e.element);
          elementType = element.nodeName;
          btn.disabled($.inArray(element.nodeName.toLowerCase(), tag_context[btn.settings.balise.toLowerCase()]) == -1);
        });
        editor.on('MouseOver', function(e) {
          el = e.target;
          name = el.tagName;
          if (name != 'P' && name != 'BODY') {
            attr = ' ';
            $.each(el.attributes, function(i, attrib) {
              attr = attr + attrib.name + "='" + attrib.value + "' ";
            });
            $("#currenttag").html(name + attr);
          }
        });
      }
      editor.addButton("gras", {
        text: "",
        balise: 'HI',
        icon: 'bold',
        onpostrender: manageButtons,
        onclick: function() {
          var selection = tinymce.activeEditor.selection.getContent();
          editor.undoManager.transact(function() {
            tinymce.activeEditor.selection.setContent("<hi rend='bold'>" + selection + "</hi>");
          });
        }
      });
      editor.addButton("italique", {
        text: "",
        balise: 'HI',
        icon: 'italic',
        onpostrender: manageButtons,
        onclick: function() {
          var selection = tinymce.activeEditor.selection.getContent({
            format: 'text'
          });
          editor.undoManager.transact(function() {
            tinymce.activeEditor.selection.setContent("<hi rend='italic'>" + selection + "</hi>");
          });
        }
      });
      editor.addButton("souligne", {
        text: "",
        balise: 'HI',
        icon: 'underline',
        onpostrender: manageButtons,
        onclick: function() {
          var selection = tinymce.activeEditor.selection.getContent({
            format: 'text'
          });
          editor.undoManager.transact(function() {
            tinymce.activeEditor.selection.setContent("<hi rend='underline'>" + selection + "</hi>");
          });
        }
      });
      editor.addButton("pleft", {
        text: "",
        balise: 'P',
        icon: 'alignleft',
        onpostrender: manageButtons,
        onclick: function() {
          var selection = tinymce.activeEditor.selection.getContent({
            format: 'text'
          });
          editor.undoManager.transact(function() {
            tinymce.activeEditor.selection.setContent("<p rend='left'>" + selection + "</p>");
          });
        }
      });
      editor.addButton("pright", {
        text: "",
        balise: 'P',
        icon: 'alignright',
        onpostrender: manageButtons,
        onclick: function() {
          var selection = tinymce.activeEditor.selection.getContent({
            format: 'text'
          });
          editor.undoManager.transact(function() {
            tinymce.activeEditor.selection.setContent("<p rend='right'>" + selection + "</p>");
          });
        }
      });
      editor.addButton("pcenter", {
        text: "",
        balise: 'P',
        icon: 'aligncenter',
        onpostrender: manageButtons,
        onclick: function() {
          var selection = tinymce.activeEditor.selection.getContent({
            format: 'text'
          });
          editor.undoManager.transact(function() {
            tinymce.activeEditor.selection.setContent("<p rend='center'>" + selection + "</p>");
          });
        }
      });
      editor.addButton("pjustify", {
        text: "",
        balise: 'P',
        icon: 'alignjustify',
        onpostrender: manageButtons,
        onclick: function() {
          var selection = tinymce.activeEditor.selection.getContent({
            format: 'text'
          });
          editor.undoManager.transact(function() {
            tinymce.activeEditor.selection.setContent("<p rend='justify'>" + selection + "</p>");
          });
        }
      });
      editor.addButton("deletePTR", {
        text: "",
        balise: 'ptr',
        icon: 'remove2',
        onpostrender: manageButtons,
        onclick: function() {
          var selection = tinymce.activeEditor.selection.getContent({
            format: 'text'
          });
          editor.undoManager.transact(function() {
            ptr = $(tinymce.activeEditor.selection.getNode()).closest('ptr');
            if (ptr.length > 0) {
              if (!confirm("Voulez-vous supprimer la balise PTR correspondant au fichier " + $(ptr).attr('target') + ' ?')) {
                return false;
              }
            } else {
              alert('Placez le curseur juste après un PTR pour le supprimer.');
              return false;
            }
            ptr.contents().unwrap();
          });
        }
      });
      $.each(boutons, function(i, bouton) {
        add(editor, bouton);
      });
    },
  }).then(function() {
    $('#items').on('change', function(event, triggerMode) {
      $('#termes-info, #file-info').html();
      $('#items-element #item-id').remove();
      $('#items-element').append("<span id='item-id'><a target='_blank' href='" + WEB_ROOT + "/items/show/" + $('#items').val() + "'> Item id : " + $('#items').val() + "</a></span>");
      $.ajax({
        url: WEB_ROOT + '/transcript/fetchfiles?itemid=' + $('#items').val(),
        dataType: 'json',
        processData: false,
        success: function(response) {
          $('#files').children().remove();
          response = Object.keys(response).map((key) => [Number(key), response[key]]);
          response.sort((a,b) => (a[1] > b[1]) ? 1 : ((a[1] < b[1]) ? -1 : 0))
          $.each(response, function(i, r) {
            $('#files').append('<option value="' + r[0] + '">' + r[1].substr(5) + '</option>');
          });
          if (typeof triggerMode != 'undefined') {
            if (typeof getUrlVars()['fileid'] != 'undefined') {
              fileid = getUrlVars()['fileid'];
              $('#files').val(fileid);
              $('#files').trigger('change');
            }
          } else {
            ids = Object.values(response);
            if (ids.length != 0) {
              $('#files').val(ids[0]);
              $('#files').change();
            }
          }
        }
      });
    });
    var pics;
    $('#files').on('change', function() {
      $('#regroup, #suppress').hide();
      $('#fileid').val($('#files').val());
      $('#files-element #file-id').remove();
      $('#transcript-rendition > *').remove();
      $('#termes-info').html();
      $('#files-element').append("<span id='file-id'><a target='_blank' href='" + WEB_ROOT + "/files/show/" + $('#files').val() + "'>File id : " + $('#files').val() + "</a></span>");
      $('#transcript-image-container > *').remove();
      tinymce.activeEditor.setContent('');
      cmEditor.setValue('');
      cmEditor.refresh();
      pictures = [];
      $.ajax({
        url: WEB_ROOT + '/transcript/fetchtranscription?fileid=' + $('#files').val(),
        dataType: 'json',
        success: function(response) {
          $('#file-info').html(response.fileinfo);
          if (response.firstfileid == $('#files').val() && ($('#userRole').html() == 'admin' || $('#userRole').html() == 'super')) {
            $('#regroup').show();
            if ($('#userRole').html() == 'super') {
              $('#suppress').show();
            }
          }
          termes = $.map(response.termes, function(e){
            return e;
          }).join(', ');
          $('#termes-info').html(termes);
          $('#transcript-image-container').append(response.image);
          $('#code-mirror-wrapper > .Codemirror-wrap').css('height', '100%');
          cmEditor.refresh();
          $transcription = $('<div/>').append(response.transcription);
          $transcription.find('i').remove();
          html = $transcription.html();
          tinymce.activeEditor.setContent(html);
          cmEditor.setValue(html);
          cmEditor.refresh();
          // Find ptr tags
          ptrs = $(html).find('ptr');
          pics = ptrs.map(function(i, ptr) {
            return $.ajax({
              url: WEB_ROOT + '/transcript/fetchfilepicture?fileid=' + $(ptr).attr('target'),
              dataType: 'json',
              success: function(picture) {
                pictures[i] = picture[0];
              },
              error: function(jqXHR, textStatus, errorThrown) {
//                 console.log(errorThrown);
                console.log('File not found : ' + $(ptr).attr('target'));
              }
            });
          });
          $.when.apply($, pics).done(function() {
            for (let i = 0; i < pictures.length; i++) {
              $('#transcript-image-container').append(pictures[i]);
            }
          });
          window.history.pushState('file' + $('#files').val(), 'Title', WEB_ROOT + '/transcript/browse?fileid=' + $('#files').val());
        }
      });
      // TODO : Rendition is only for EMAN. For now.
      if (window.location.host == 'eman-archives.org') {
        $.ajax({
          url: WEB_ROOT + '/transcript/fetchrendition?fileid=' + $('#files').val(),
          dataType: 'json',
          success: function(response) {
            $('#transcript-rendition').html(response.transcription);
            if (typeof response.messages != 'undefined') {
              $('#messages').html(response.messages + ' ');
            } else {
              $('#messages').html('');
            }
            $('#file-info').html(response.fileinfo);
            var checkExist = setInterval(function() {
              if ($(urlhash).length) {
                if ($(urlhash).length > 1) {
                  scrollobj = $(urlhash).eq(1);
                } else {
                  scrollobj = $(urlhash);
                }
                $('body, html').scrollTop($(scrollobj).offset().top - 200, 'fast');
                $(scrollobj).next().addClass('highlight');
                clearInterval(checkExist);
              }
            }, 100);
          },
          error: function(error) {
            $('transcript-rendition').html(error);
          }
        });
      }
    });

    $('body').on('click', '.mce-tinymce > .mce-container-body .mce-btn', function() {
      activate_menu_items();
    });
    $('body').on('mouseenter', '.mce-tinymce > .mce-container-body .mce-btn', function() {
      activate_menu_items();
    });
    $('#transcript-rendition').on('mouseenter', '.highlight', function() {
      $('.highlight').removeClass(('highlight'));
    });

    $('#import').on('change', function() {
      var formData = new FormData();
      formData.append('file', $('input[type=file]')[0].files[0]);
      $.ajax({
        url: WEB_ROOT + '/admin/transcript/importtranscription',
        type: 'POST',
        data: formData,
        beforeSend: function() {},
        success: function(data) {
          $('#import-output').html(data);
          $(window).off('beforeunload');
        },
        cache: false,
        contentType: false,
        processData: false
      });
    });

    $('#toolbar').on('click', '#regroup', function() {
        $.ajax({
          url: WEB_ROOT + '/transcript/regroup?itemid=' + $('#items').val(),
          dataType: 'json',
          success: function(response) {
            cmEditor.setValue(XmlToTiny(response, 'tiny'));
            tinymce.activeEditor.setContent(XmlToTiny(response));
          },
          error: function(error) {
            console.log(error);
          }
        });

    });

    $('#toolbar').on('click', '#suppress', function() {
        $.ajax({
          url: WEB_ROOT + '/transcript/suppress?itemid=' + $('#items').val(),
          dataType: 'json',
          success: function(response) {
            $('#messages').html(response);
          },
          error: function(error) {
            console.log(error);
          }
        });

    });
    $('#import-output').on('click', '#bt-import', function() {
      importOk = true;
      mapping = {};
      $('#transcript-mapping .orig').each(function(i, v) {
        orig = $(this).html();
        dest = $(this).parent().parent().find('select.dest').val();
        if (dest === 'none') {
          $(this).parent().parent().find('select.dest').parent().addClass('red');
          mapping[orig] = 'nomap';
        } else {
          $(this).parent().parent().find('select.dest').parent().removeClass('red');
          if (orig != dest) {
            mapping[orig] = dest;
          }
        }
      });
      if (importOk) {
        if (!confirm("Cette opération va remplacer la transcription actuelle par celle du fichier importé.\n\nVoulez-vous continuer (OK) ou annuler (Annuler) ?")) {
          return false;
        }
        mes = "Import OK.";
        $.ajax({
          type: "POST",
          data: {
            xmlFile: $('#xml-file-path').text(),
            map: mapping,
            fileId: $('#files').val()
          },
          url: WEB_ROOT + '/transcript/do-import',
          dataType: 'xml',
          beforeSend: function() {
            $('#messages #message').remove();
            $('#messages').prepend("<span id='message'>Import en cours, merci de patienter.</span>");
          },
          success: function(response) {
            $('#messages #message').remove();
            $('#messages').prepend("<span id='message'>'Import was successful !'</span>");
            xml = (new XMLSerializer()).serializeToString(response);
            tinymce.activeEditor.setContent(XmlToTiny(xml));
          },
          error: function(msg) {
            console.log(msg);
          }
        });
      } else {
        mes = "Import not OK : All tags must have a destination."
      }
      $('#messages #message').remove();
      $('#messages').prepend("<span id='message'>" + mes + "</span>");

    });
    $('#import-output').on('change', '.dest', function() {
      if ($(this).val() != 'none') {
        $(this).parent().removeClass('red');
      } else {
        $(this).parent().addClass('red');
      }
    });

    $('#transcript-show-source').click(function() {
      if ($('#code-mirror-wrapper').is(":visible")) {
        code = cmEditor.getValue();
        tinymce.activeEditor.setContent(XmlToTiny(code, 'html'));
        $('#TranscriptionForm').show();
        $('#code-mirror-wrapper').hide();
        $('#transcript-show-source').html('Source');
        $('#transcript-validate, #validation-response').hide();
      } else {
        var code = tinymce.activeEditor.getContent();
        cmEditor.setValue(XmlToTiny(code, 'tiny'));
        $('#TranscriptionForm').hide();
        $('#transcript-validate, #validation-response').show();
        $('#code-mirror-wrapper').show();
        $('#code-mirror-wrapper > .Codemirror-wrap').css('height', '100%');
        cmEditor.refresh();
        $('#transcript-show-source').html('Éditeur');
      }
    });

    $("#transcript-validate").on('click', function(e) {
      $(this).attr('disabled', true);
      $(this).html('Merci de patienter ...')
      e.preventDefault();
      tei = JSON.stringify(cmEditor.getValue());
      headerSize = 22;
      $.ajax({
        type: "POST",
        data: {
          xml: tei
        },
        url: WEB_ROOT + '/transcript/validate',
        dataType: 'json',
        beforeSend: function() {
          $("#validation-response").html('Validation en cours, merci de patienter ...');
          $("#validation-response").css('background-color', '#e19735');
        },
        success: function(response) {
          if (response[0] != 'OK') {
            var html = '<ul class="transcript-xml-errors">';
            $.each(response, function(i, val) {
              html = html + '<li class="level-' + val.level + '"> Ligne ' + (val.line - headerSize) + ' Colonne ' + val.column + ' [code ' + val.code + '] : ' + val.message + '</li>';
            });
            html = html + '</ul>';
            $("#validation-response").css('background-color', 'transparent');
          } else {
            html = 'Document valide !';
            $("#validation-response").css('background-color', '#20c003');
          }
          $("#validation-response").html(html);
          $("#transcript-validate").removeAttr('disabled');
          $("#transcript-validate").html('Valider')
        },
        error: function(msg) {
          console.log(msg);
        }
      });
    });

    // Trigger files list population
    $('#items').trigger('change', ['pageLoad'])

    $('#refresh-rendition').click(function() {
      $('#items').trigger('change');
    });

  });

// TODO : Is this code useless ?
/*
  if (typeof getUrlVars()['fileid'] != 'undefined') {
    fileid = getUrlVars()['fileid'];
    $.ajax({
      url: WEB_ROOT + '/transcript/fetchitemid?fileid=' + fileid,
      dataType: 'json',
      success: function(response) {
        $('#items').val(response);
        $('#items').trigger('change', ['pageLoad']);
      },
      error: function(error) {
        console.log('Error fetching Item Id.');
      }
    });
  }
*/
  $.fn.xml4tei({
    helpLangs: ['fr'],
    helpJsondriver: "proxy",
    helpProxy: "https://cors.bridged.cc",
    buttonsPanel: true,
    helpBtn: true,
    loadExamples: false,
    examplesBtn: false,
    validatorBtn: false
  });

});