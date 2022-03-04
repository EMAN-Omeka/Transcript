window.jQuery = window.$ = $ = jQuery;
var visu;

boutons = [];

$(document).ready(function() {

  WEB_ROOT = $('#phpWebRoot').html();

  urlhash = 'span' + window.location.hash;

  $('#items').change(function(event, triggerMode) {
    $('#items-element #item-id').remove();
    $('#items-element').append("<span id='item-id'><a target='_blank' href='" + WEB_ROOT + "/items/show/" + $('#items').val() + "'> Item id : " + $('#items').val() + "</a></span>");
    $.ajax({
      url: WEB_ROOT + '/transcript/fetchfiles?itemid=' + $('#items').val(),
      dataType: 'json',
      success: function(response) {
        $('#files').children().remove();
        response = Object.keys(response).map((key) => [Number(key), response[key]]);
        response.sort((a,b) => (a[1] > b[1]) ? 1 : ((a[1] < b[1]) ? -1 : 0))
        $.each(response, function(i, r) {
          $('#files').append('<option value="' + r[0] + '">' + r[1].substr(5) + '</option>');
        });
        $('#trancript-pictures > *').remove();
        $('#transcript-image-container > *').remove();
        if (typeof triggerMode != 'undefined') {
          if (typeof getUrlVars()['fileid'] != 'undefined') {
            fileid = getUrlVars()['fileid'];
            $('#files').val(fileid);
            $('#files').trigger('change');
          }
        } else {
          ids = Object.keys(response);
          if (ids.length != 0) {
            $('#files').val(ids[0]);
            $('#files').trigger('change');
          }
        }
      }
    });
  });

  $('#files').on('change', function() {
    $('#fileid').val($('#files').val());
    $('#files-element #file-id').remove();
    $('#trancript-rendered').html('');
    $('#termes-info').html();
    $('#files-element').append("<span id='file-id'><a target='_blank' href='" + WEB_ROOT + "/files/show/" + $('#files').val() + "'>File id : " + $('#files').val() + "</a></span>");
    pictures = [];
    if (window.location.host == 'eman-archives.org') {
      $.ajax({
        url: WEB_ROOT + '/transcript/fetchrendition?fileid=' + $('#files').val(),
        dataType: 'json',
        success: function(response) {
          window.history.pushState('file' + $('#files').val(), 'Title', WEB_ROOT + '/transcript/browse?fileid=' + $('#files').val());
          $('#trancript-rendered').html(response.transcription);
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
            }, 100); // check every 100ms
          $('#file-info').html(response.fileinfo);
          termes = $.map(response.termes, function(e){
            return e;
          }).join(', ');
          $('#termes-info').html(termes);
          $('#trancript-pictures > *').remove();
          $('#transcript-image-container > *').remove();
          $.ajax({
            url: WEB_ROOT + '/transcript/fetchfilepicture?fileid=' + $('#files').val(),
            dataType: 'json',
            success: function(picture) {
              $('#trancript-pictures').append(picture);
            }
          });
          // Find ptr in rendition when public
          ptrs = $('#trancript-rendered').find('.tei-ptr span');
          var pics = ptrs.map(function(i, ptr) {
            return $.ajax({
              url: WEB_ROOT + '/transcript/fetchfilepicture?fileid=' + $(ptr).html(),
              dataType: 'json',
              success: function(picture) {
                pictures[i] = picture;
              }
            });
          });
          $.when.apply($, pics).done(function() {
            for (let i = 0; i < pictures.length; i++) {
              $('#trancript-pictures').append(pictures[i]);
            }
          });
        },
        error: function(error) {
          $('#trancript-rendered').html(error);
        }
      });
    }
  });

  $('#trancript-rendered').on('mouseenter', '.highlight', function() {
    $('.highlight').removeClass(('highlight'));
  });

  // Trigger files list population
  $('#items').trigger('change', ['pageLoad']);

});