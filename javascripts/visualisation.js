var buttons;
var icones;
window.jQuery = window.$ = jQuery;
function get_buttons_list() {
  var boutons;
  $.ajax({
    url: $('#phpWebRoot').html() + '/plugins/Transcript/javascripts/buttons.json',
    async: false,
    dataType: "json",
    success : function(data) {
      boutons = data;
    }
  });
  return boutons.buttons;
}
$(document).ready(function() {
  $('#transcription-full').load(function(){
  var iframe = $('#transcription-full').contents();

  // On masque les éléments de popup
	iframe.find('expan, reg, figdesc').addClass('inv');

	// Masquage des icones
	iframe.find("i").hide().addClass('inv');
	iframe.find(".fig i, .ref i").addClass('vis');

	// Traitement des ref (liens)
	iframe.find('ref').click(function(e) {
		window.open($(this).attr('target'));
	});

	// Traitement des notes ADD top et bottom
	iframe.find("add[place=top]").prependTo('body body');
	iframe.find("add[place=bottom]").appendTo('body body');

	iframe.find("add, metamark").each (function (x, elem) {
  	$(elem).html($.trim($(elem).html()));
	});

	// Traitement des abréviations : on montre choice et expan, on cache abbr
	iframe.find("choice, expan").removeClass('inv');
	iframe.find("abbr").addClass('inv');

	// Traitement des substitutions : on montre choice et reg, on cache orig
	iframe.find("orig").addClass('inv');
	iframe.find("reg").removeClass('inv');

	// Traitement des notes
	iframe.find("note").addClass('inv');

	// Affichage des icônes pour le niveau 1
  var icones;
  var buttons_icons = [];

  buttons = get_buttons_list();

 	$.each(buttons, function (i, button) {
   	buttons_icons[button.cl] = button.icon;
 	});
	$.each(transcript_options, function(nom, o) {
		if ($.inArray('icones', o) > -1) {
    	icones = icones + ',.fa-' + buttons_icons[nom];
    }
	});

  var icons_lvl1 = 'vide';
  var icons_lvl2 = 'vide';

  $.each(ilvl, function(tagName, levels) {
    if (levels[0] == 'un' || levels[1] == 'un') {
      icons_lvl1 = icons_lvl1 + ',.fa-' + buttons_icons[tagName];
    }
    if (levels[0] == 'deux' || levels[1] == 'deux')  {
      icons_lvl2 = icons_lvl2 + ',.fa-' + buttons_icons[tagName];
    }
  });

  iframe.find('.fa').hide();
  iframe.find(icons_lvl1).removeClass('inv').addClass('vis').show();

	// Affichage des commentaires pour le niveau 1
  create_comments();
  display_comments('un');

	// Construction et gestion de la popup
  selector = "figure, figdesc, app, signed, sic, ref, handshift, orig, abbr, gap, orig, expan, span, persname";

	iframe.on('mouseenter', selector, function(e) {
		tagName = $(this).prop("tagName");
		// Pas de popup sur Mathml
		if (tagName.substring(0, 3) == "mml") {
			return false;
		}
		e.stopPropagation();
		var poptext = "";
		var popobject;

		if ($(this).get(0).tagName == 'figure') {
			poptext = $(this).find('figdesc').text();
		} else if ($($(this).get(0)).hasClass('note')) {
      popobject = $(this).next();
    } else if ($.inArray($(this).get(0).tagName, ['add', 'lg', 'unclear', 'signed', 'sic'])  != -1) {
			popobject = $(this);
		} else if ($(this).get(0).tagName == 'persname') {
			poptext = $(this).attr('ref');
		} else if ($(this).get(0).tagName == 'ref') {
			poptext = $(this).attr('target');
		} else if ($(this).get(0).tagName == 'handshift') {
			poptext = 'Scribe : ' + $(this).attr('scribe');
		} else if ($(this).get(0).tagName == 'orig') {
			popobject = $(this).parent().find('reg');
		} else if ($(this).get(0).tagName == 'abbr') {
  		popobject = $(this).parent().find('expan');
		} else if ($.inArray($(this).get(0).tagName, ['label', 'title', 'input'])  != -1) {
			popobject = $(this).parent().find('del, gap, orig');
		} else {
  		return false;
		}
	  var newSpan = document.createElement("span");
		if (popobject) {
      var newContent = popobject.clone().removeClass('inv');
  		newSpan.appendChild(newContent[0]);
		} else if (poptext.trim() != "") {
  		newSpan = document.createTextNode(poptext);
    }
		$(this).append('<div id="popup" style="overflow:visible;display:block;"></div>');
		iframe.find("#popup").prepend(newSpan);
	});

	iframe.on('mouseleave', selector, function(e) {
		iframe.find('#popup').remove();
	});

  //
  // Affichage de la version riche.
  //
  iframe.find('#pleinecran').click(function() {
		if ($(this).prop('checked')) {
  		$('#transcript-zoom').css("width", '0%');
  		$('#right').css("width", '98%');
    } else {
  		$('#transcript-zoom').css("width", '49%');
  		$('#right').css("width", '49%');
    }
  });
  iframe.find('#showMarkup').click(function() {
    $('.inv').hide();
    // Hide everything
		iframe.find('i').addClass('inv').removeClass('vis').hide();
		if ($(this).prop('checked')) {
  		// Show level 2 icons
      iframe.find(icons_lvl2).removeClass('inv').addClass('vis').show();

      // Cas ABBR + EXPAN et REG + ORIG
   	  iframe.find('expan').each(function(){
        $(this).addClass('inv');
        $(this).parent().find("abbr, abbr i").removeClass('inv').addClass('signal');
      });
      iframe.find('reg').each(function(){
        $(this).addClass('inv');
        $(this).parent().find("orig, orig i").removeClass('inv').addClass('signal');
      });
      // Affichage et masquage des commentaires pour le niveau 2
      display_comments('deux');
    } else {
  		// Show level 1 icons
      iframe.find(icons_lvl1).removeClass('inv').addClass('vis').show();
      iframe.find('expan').each(function(){
        $(this).removeClass('inv');
        $(this).parent().find("abbr, abbr i").addClass('inv').removeClass('signal');
      });
      iframe.find('reg').each(function(){
        $(this).removeClass('inv');
        $(this).parent().find("orig, orig i").addClass('inv').removeClass('signal');
      });
      // Affichage et masquage des commentaires pour le niveau 1
      display_comments('un');		}
  });

  function create_comments() {
    var niveaux;
    iframe.find("*[data!='']").each(function() {
    	if (typeof($(this).attr('data')) != 'undefined') {
      	tagName = $(this).prop('tagName');
      	if(tagName == 'handshift') {tagName = 'handShift';}
      	if(tagName == 'placename') {tagName = 'placeName';}
      	if(tagName == 'persname') {tagName = 'PersName';}
      	niveaux = '';
      	if (typeof clvl[tagName] != 'undefined') {
          if (clvl[tagName][0] == 'un' || clvl[tagName][1] == 'un') {
            niveaux = 'un';
          }
          if (clvl[tagName][0] == 'deux' || clvl[tagName][1] == 'deux') {
            niveaux = niveaux + ' deux';
          }
        }
      	$("<span class='transcript-visu " + niveaux + " " + tagName + " inv'>(" + $(this).attr('data') + ")</span>").insertBefore($(this));
    	}
  	});
  }

  function display_comments(level) {
    iframe.find(".transcript-visu").addClass('inv').hide();
    iframe.find("." + level).removeClass('inv').show();
  }

});

  // Nous ne voulons pas de l'affichage automatique du title en popup
  $('[title]').each( function() {
    var $this = $(this);
    $this.data('title',$this.attr('title'));
    $this.removeAttr('title');
  });
});