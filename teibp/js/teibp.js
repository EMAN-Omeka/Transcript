$ = jQuery;

$(document).ready(function() {
	// On masque les éléments de popup
	$('abbr, orig, figdesc').addClass('inv');
//	$('note').attr('visibility', 'hidden');
//	$('note::before').attr('visibility', 'visible');
	
	// Traitement des ref (liens)
	$('ref').click(function(e) {		
		window.open($(this).attr('target'));
	});
	// Traitement des notes ADD top et bottom
	// TODO : tenir compte des options saisies en admin
	$("add[place=top]").prependTo('body body');
	$("add[place=bottom]").appendTo('body body');
	
	// Construction de la popup
	selector = ':not(TEI, html, body, text, div, p, #popup, i, item, l, app, choice, hi, sic, cell, list, item, foreign, lg, signed, expan, teiHeader)';
// 	selector = ':not(TEI, html, body, text, div, p, #popup, i, item, l, app, choice, hi, add, sic, cell, list, item, foreign, lg, signed, expan, teiHeader)';
	$(document).on('mouseenter', selector, function(e) {
		tagName = $(this).prop("tagName");
		// Pas de popup sur Mathml
		if (tagName.substring(0, 3) == "mml") {
			return false;
		}
		e.stopPropagation();

		var poptext = "";
		if ($(this).get(0).tagName == 'fig') {
			poptext = $(this).find('figdesc').text();
		} else if ($.inArray($(this).get(0).tagName, ['lg', 'app', 'unclear', 'signed', 'sic'])  != -1) {
// 		} else if ($.inArray($(this).get(0).tagName, ['add', 'lg', 'app', 'unclear', 'signed', 'sic'])  != -1) {
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
		} else if ($.inArray($(this).get(0).tagName, ['label', 'title', 'input'])) {
			poptext = $(this).parent().find('expan, gap, orig').text();
// 			poptext = $(this).parent().find('expan, del, gap, orig').text();
		}
		if (poptext.trim() == '') {
			poptext = "(" + $(this).find('i').data('title') + ")"
		}
	  var newSpan = document.createElement("span"); 
	  var newContent = document.createTextNode(poptext);
		newSpan.appendChild(newContent);
		$(this).append('<div id="popup"></div>');
		$(newSpan).prependTo('#popup');		
	});			
	$(document).on('mouseleave', selector, function(e) {
		$('#popup').remove();
	});		
	
//Icones simple :  figure, ref
//Icones riche : lb, handshift, figure, ref

// TODO : tenir compte des options saisies en admin.
	$("i").hide().addClass('inv');
	$(".fig i, .ref i").addClass('vis');
	// Affichage de la version riche.
	$('#showMarkup').click(function() {
		if ($(this).prop('checked')) {
			$(".lb i, .handShift i, abbr, orig").addClass('vis');
			$("expan, reg").addClass('inv');
		} else {
			$(".lb i, .handShift i,  abbr, orig").removeClass('vis');
			$("expan, reg").removeClass('inv');
		}
	});
//	$('#showMarkup').click(function() {
//		if ($(this).prop('checked')) {
//			$('.fa').removeClass('inv');						
//			$('.del').addClass('vis');	
//			$('expan, orig, note, del').removeClass('inv');
//			$("lb").addClass('line-break');
//		} else {
//			$('.fa').addClass('inv');									
//			$('.del').removeClass('vis');	
//			$('expan, orig, note, del, figdesc').addClass('inv');		
//		}
//	});
	// Nous ne voulons pas de l'affichage automatique du title en popup	
	$('[title]').each( function() {
    var $this = $(this);
    $this.data('title',$this.attr('title'));
    $this.removeAttr('title');	
	});	
});
