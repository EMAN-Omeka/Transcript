var buttons;
var icones;
window.jQuery = window.$ = jQuery;

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
	
	// Traitement des abréviations : on montre choice et expan, on cache abbr
	iframe.find("choice").removeClass('inv');
	iframe.find("abbr").addClass('inv');
	iframe.find("expan").removeClass('inv');
	
	// Traitement des substitutions : on montre choice et reg, on cache orig
	iframe.find("orig").addClass('inv');
	iframe.find("reg").removeClass('inv');


	// Affichage des icônes pour le niveau 1
  var icones;
  var buttons_icons = [];

 	$.each(buttons, function (i, button) {
   	buttons_icons[button.cl] = button.icon;
 	});
	$.each(transcript_options, function(nom, o) {
		if ($.inArray('icones', o) > -1) {
    	icones = icones + ',.fa-' + buttons_icons[nom];
    }
	});
	

  var icons_lvl1 = 'toto';
  var icons_lvl2 = 'titi';
  
  $.each(ilvl, function(tagName, levels) {
    if (levels[0] == 'un') {      
      icons_lvl1 = icons_lvl1 + ',.fa-' + buttons_icons[tagName];
    }
    if (levels[0] == 'deux')  {
      icons_lvl2 = icons_lvl2 + ',.fa-' + buttons_icons[tagName];
    }
  });
  
  iframe.find('.fa').hide();
  iframe.find(icons_lvl1).removeClass('inv').addClass('vis').show();
  
	// Affichage des commentaires pour le niveau 1
  create_comments();
  display_comments('un');
  
	// Construction et gestion de la popup
  selector = "figure, figdesc, add, lg, app, unclear, signed, sic, note, ref, handshift, orig, abbr, del, gap, orig";
  
	iframe.on('mouseenter', selector, function(e) {
		tagName = $(this).prop("tagName");
		// Pas de popup sur Mathml
		if (tagName.substring(0, 3) == "mml") {
			return false;
		}
		e.stopPropagation();
		var poptext = "";
		if ($(this).get(0).tagName == 'figure') {
			poptext = $(this).find('figdesc').text();
		} else if ($.inArray($(this).get(0).tagName, ['add', 'lg', 'app', 'unclear', 'signed', 'sic'])  != -1) {
			poptext = $(this).text();
		} else if ($(this).get(0).tagName == 'note') {
			poptext = $(this).text();
		} else if ($(this).get(0).tagName == 'ref') {
			poptext = $(this).attr('target');
		} else if ($(this).get(0).tagName == 'handshift') {
//				$('#popup').append('<div>Medium : ' + $(this).attr('medium') + '</div>\n');
			poptext = 'Scribe : ' + $(this).attr('scribe');
		} else if ($(this).get(0).tagName == 'orig') {
			poptext = $(this).parent().find('reg').text();
		} else if ($(this).get(0).tagName == 'abbr') {
  		poptext = $(this).parent().find('expan').text();
		} else if ($.inArray($(this).get(0).tagName, ['label', 'title', 'input']) != -1) {
			poptext = $(this).parent().find('del, gap, orig').text();
		}
    if (poptext.trim() != "") {
  	  var newSpan = document.createElement("span"); 
  	  var newContent = document.createTextNode(poptext);
  		newSpan.appendChild(newContent);
  		$(this).append('<div id="popup"></div>');
  		iframe.find("#popup").prepend(newSpan);		      
    }
	});			
	
	iframe.on('mouseleave', selector, function(e) {
		iframe.find('#popup').remove();
	});		

  //
  // Affichage de la version riche.
  //
  
  iframe.find('#showMarkup').click(function() {	      
    $('.inv').hide();  
   	// Cas ABBR + EXPAN et REG + ORIG
		if ($(this).prop('checked')) {
      iframe.find(icons_lvl1).addClass('inv').removeClass('vis').hide();
      iframe.find(icons_lvl2).removeClass('inv').addClass('vis').show();
      iframe.find('expan').each(function(){
        $(this).addClass('inv');
        $(this).parent().find("abbr").removeClass('inv').addClass('signal'); 
      });	     
      iframe.find('reg').each(function(){
        $(this).addClass('inv');
        $(this).parent().find("orig").removeClass('inv').addClass('signal'); 
      });	            
      // Affichage et masquage des commentaires pour le niveau 2
      display_comments('deux');      		
    } else {
      iframe.find(icons_lvl1).removeClass('inv').addClass('vis').show();
      iframe.find(icons_lvl2).addClass('inv').removeClass('vis').hide();
      iframe.find('expan').each(function(){
        $(this).removeClass('inv');
        $(this).parent().find("abbr").addClass('inv').removeClass('signal'); 
      });	  
      iframe.find('reg').each(function(){
        $(this).removeClass('inv');
        $(this).parent().find("orig").addClass('inv').removeClass('signal'); 
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
      	niveaux = '';
      	if (typeof clvl[tagName] != 'undefined') {      	
        	console.log(clvl[tagName]);
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