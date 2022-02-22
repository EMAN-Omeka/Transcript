
var completeAfter=function(cm, pred) {
  var cur = cm.getCursor();
	if (!pred || pred()) setTimeout(function() {
	  if (!cm.state.completionActive)
		cm.showHint({completeSingle: false});
	}, 100);
	return CodeMirror.Pass;
}

var completeIfAfterLt=function(cm) {
	return completeAfter(cm, function() {
	  var cur = cm.getCursor();
	  return cm.getRange(CodeMirror.Pos(cur.line, cur.ch - 1), cur) == "<";
	});
}

var completeIfInTag=function(cm) {
	return completeAfter(cm, function() {
	  var tok = cm.getTokenAt(cm.getCursor());
	  if (tok.type == "string" && (!/['"]/.test(tok.string.charAt(tok.string.length - 1)) || tok.string.length == 1)) return false;
	  var inner = CodeMirror.innerMode(cm.getMode(), tok.state).state;
	  return inner.tagName;
	});
}

function getCmJson(schema) {
  var tags;
    $.ajax({
      'async': false,
      url: schema,
      cache: false,
      dataType: 'xml',
      success: function(response) {
          //parse the xml schema to create a json Object according to CodeMirror style
          tags = $.fn.xml4teiSchema2json({xml:response})
      }
    });
  return tags;
}

function resizeIframe(obj) {
  obj.style.height = obj.contentWindow.document.firstChild.scrollHeight + 'px';
}

$.fn.redraw = function(){
  $(this).each(function(){
    var redraw = this.offsetHeight;
  });
};

// Pour que le code des boutons soit valide pour TinyMCE
function add_valid_elements() {
	var valid_elements = 'xml:id';
	$.each(buttons, function(i, button) {
		attributes = "[title|data";
		if (typeof button.att != "undefined") {
  		$.each(button.att, function(j, attribute) {
    		attributes = attributes + "|" + attribute.att;
  		});
		}
		valid_elements = valid_elements + button.cl.toLowerCase() + attributes + "],";
	});
	valid_elements = valid_elements + 'ttable,ffigure,headd,l[xml::id|n]';
	return valid_elements;
}

// Ajout des boutons à la toolbar
function fill_toolbar() {
  listBoutons = '';
	$.each(boutons, function(i, button) {
		listBoutons = listBoutons + " " + button.cl;
	});
	return listBoutons;
}

//Ajout des boutons au dropdown menu
function fill_menu(contexte) {
	var liste_boutons = '';
// 	boutons = get_buttons_list();
	$.each(boutons, function(i, button) {
  // TODO : revoir code de sélection des tags actifs dans admin
/*
		if (typeof transcript_options[button.cl] === "undefined") {
  		return true;
		}
*/
		if (button.menu == contexte) {
			liste_boutons = liste_boutons + " " + button.cl;
		}
		if (typeof button.menu === "undefined" && contexte == 'struct') {
			liste_boutons = liste_boutons + " " + button.cl;
		}
	});
  return liste_boutons;
}

function activate_menu_items() {
	// Element type selected in TinyMCE
	element = tinymce.activeEditor.selection.getNode();
// TODO : À quoi servait ce code ????
/*
	if (element.constructor.name === "HTMLUnknownElement") {
  	element = element.parentNode;
	}
*/
	elementType = element.nodeName;
	// Tous les items visibles : ceux qui viennent d'apparaître suite au clic sur le menu
	menuItems = $('.mce-menu-item:visible span');
	$.each(menuItems, function(i, menuItem) {
  	if ($(menuItem).text() == "Insérer/modifier un lien") {
  		$(menuItem).parent().addClass('tei-ko');
    	return;
  	}
  	menuName = $(menuItem).text();
		var balise = menuName.substr(0, menuName.indexOf(' '));
		var contexte = tag_context[balise];
		if (typeof contexte != 'undefined') {
			// Remplacement des balises "spéciales" pour qu'elles soient un contexte valide
			i = contexte.indexOf('head');
			contexte[i] = 'headd';
			i = contexte.indexOf('title');
			contexte[i] = 'tittle';
			i = contexte.indexOf('figure');
			contexte[i] = 'ffigure';
			i = contexte.indexOf('table');
			contexte[i] = 'ttable';
			// TODO : gérer les maj/min => handShift
			if ($.inArray(elementType.toLowerCase(), contexte) > -1 || balise == 'div' && elementType == 'body') {
				// On le marque comme visible si le contexte l'autorise ...
				$(menuItem).parent().addClass('tei-ok');
			} else {
				// ... et invisible si non ...
				$(menuItem).parent().addClass('tei-ko');
			}
		}
	});
	 // ... et enfin on applique les propriétés à tous les items en même temps.
	$('.tei-ok').show();
	$('.tei-ko').hide();
}

function add(editor, button) {
	// Menu par défaut
	if (typeof button.menu === "undefined") {
		var contexte = 'struct';
	} else {
		var contexte = button.menu;
	}
	// Tags interdits : on remplace la balise temporaire par sa vraie désignation (headd => HEAD)
	if (button.cl == 'headd') {
		buttonName = 'head (Entête)';
	} else if (button.cl == 'tittle') {
		buttonName = 'title (Titre)';
	} else if (button.cl == 'ffigure') {
		buttonName = 'figure (Illustration ou schéma)';
	} else {
		buttonName = button.cl;
	}

  buttonName = buttonName + ' (' + button.ti + ')';

	editor.addMenuItem(button.cl, {
    text: buttonName,
    context: contexte,
    bouton: button, // Paramètre maison pour accès dans onClick
    classes: button.cl,
    disabledStateSelector : ':not(a)',
		onclick: function(elem) {
 			bouton = this.settings.bouton;
      tagName = button.cl;
      // TODO : à revoir
      if (tagName == "table") {
        tagName = "ttable";
      }
      tagName = tagName.toLowerCase();
  		var attributes = ''
			var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
			if (bouton.att.length > 0) {
//         editor.undoManager.transact(function() {
  			// Attributes, Forms, etc.
        editor.windowManager.open({
          title: 'Attributs pour ' + bouton.cl,
          body: add_form_element(bouton),
          onsubmit: function(e) {
  	 				editor.undoManager.transact(function() {
  	 					$.each(e.data, function(name, value) {
    	 					// Stockage fileId pour multipage
    	 					if (tagName == 'ptr' && name == 'target') { fileId = value; }
    	 					if (value != '') {
      						attributes = attributes + name + '="' + value + '" ';
    	 					}
  						});
  						if (tagName == 'head') {tagName = 'headd';}
  						if (tagName == 'table') {tagName = 'ttable';}
              tinymce.activeEditor.selection.setContent("<" + tagName + " " + attributes + ">" + selection + "</" + tagName + ">");
              if (tagName == 'ptr') {
                $.ajax({
                  async:false,
                  url: WEB_ROOT + '/transcript/fetchfilepicture?fileid=' + fileId,
                  dataType: 'json',
                  success: function(picture) {
                   $('#transcript-image-container').height($('#transcript-image-container').height() + 800);
                   $('#transcript-image-container').append(picture);
                  }
                });
                $('#transcript-zoom').scrollTop($('#transcript-image-anchor-' + fileId).offset().top - $('#transcript-zoom').offset().top +  + $('#transcript-zoom').scrollTop() - 20, 'slow');
              }
  	 			  });
          }
        });
      } else {
        editor.undoManager.transact(function() {
          tinymce.activeEditor.selection.setContent("<" + tagName + ">" + selection + "</" + tagName + ">");
        });
      }
		}
	});
};

// Given button, return corresponding form element
function add_form_element(button) {
  // TODO : à externaliser dans un fichier par instance ?
  var traductions = {"rend" : "rendu", "target" : "cible", "scribe" : "auteur", "type" : "type", "reason" : "raison", "cert" : "certitude", "place" : "emplacement", "when" : "quand", "break" : "break", "scribe" : "auteur", "xmllang" : "langue", "medium": "support", "place": "endroit", "pencil": "Crayon", "handPencil": "Crayon à papier", "TypeWriter" : "Machine à écrire", "hand" : "Manuscrit", "blue" : "bleu", "black" : "noir", "purple" : "violet", "red" : "rouge", "marginRight":"Marge droite", "marginLeft":"Marge gauche", "top":"haut", "bottom":"En bas", "above":"Au-dessus", "below":"Au-dessous","inline":"Sur la ligne", "wit":"Variante (wit)"};
  form_elements = [];
  $.each(button.att, function(i, att) {
    var values = [];
    label = att.att;
    if (typeof traductions[att.att] != 'undefined') {
      label = traductions[att.att];
    }
    if (button.cl == 'ptr' && att.att == 'target') {
  		$('#files option').each(function(index, file) {
    		values.push({text: file.innerHTML, value: file.value});
  		});
  		element_type = 'listbox';
      form_elements.push({type: element_type, name: att.att, label: label, values: values});
      return true;
    }
  	if (att.values[0].texte != '' && att.att != 'xml:id') {
  		$.each(att.values[0].valeur.split(','), function(j, value) {
        texte = value;
        if (typeof traductions[value] != 'undefined') {
          texte = traductions[value];
        }
    		values.push({text: texte, value: value});
  		});
  		element_type = 'listbox';
      form_elements.push({type: element_type, name: att.att, label: label, values: values});
    } else {
  		element_type = 'textbox';
  		value = '';
  		if (att.att == 'source') {
    		value = 'source';
  		}
      form_elements.push({type: element_type, name: att.att, label: label, value: value});
    }
  });
  return form_elements;
}

function XmlToTiny(code, mode) {
  // TODO : Automatiser la détection et conversion de majuscules dans les tags == la constituion de ces tableaux
  xml = ['handShift', 'persName', 'placeName', 'cRef', 'mimeType', 'table', 'head', 'castList', 'castItem'];
  tiny = ['handshift', 'persname', 'placename', 'cref', 'mimetype', 'ttable', 'headd', 'castlist', 'castitem'];
  if (mode == 'tiny') {
    $(tiny).each(function (i, s) {
      exp = new RegExp(s, 'g');
      code = code.replaceAll(exp, xml[i]);
    });
  } else {
    $(xml).each(function (i, s) {
      exp = new RegExp(s, 'g');
      code = code.replaceAll(exp, tiny[i]);
    });
  }
  return (code);
}

function removeElements (text, selector) {
    var wrapped = $("<div>" + text + "</div>");
    wrapped.find(selector).remove();
    return wrapped.html();
}

function get_real_parent(e) {
  if (typeof e.tagName != 'undefined') {
    if (['lb','ptr','pb','cb','handShift','milestone'].indexOf(e.tagName.toLowerCase()) !== -1) {
    	e = get_real_parent(e.parentNode);
    }
  }
	return e;
}

function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      if (typeof hash[1] != 'undefined') {
        vars.push(hash[0]);
        vars[hash[0]] = hash[1].split('#')[0];
      }
    }
    return vars;
}
function alphabetizeList(listField) {
   var sel = listField;
   var selected = sel.val(); // cache selected value, before reordering
   var opts_list = sel.find('option');
   opts_list.sort(function(a, b) {
      return $(a).val() > $(b).val() ? 1 : -1;
   });
   sel.html('').append(opts_list);
   sel.val(selected); // set cached selected value
}