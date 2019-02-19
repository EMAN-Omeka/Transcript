window.jQuery = window.$ = jQuery;
var buttons;
var visu;
  
jQuery(document).ready(function() {

  tinymce.init({
    selector: '#transcription',
    language: 'fr_FR',
//    inline: true,    
    height: 500,
    theme: 'modern', //ens
    menubar: "tools teistructure teimef teiannot",
    element_format : 'xhtml',
    entities : '160,nbsp,162,cent,8364,euro,163,pound,Sum',
    menu: {
    		teistructure: {title : ' Structure', items : fill_menu('struct')},
  			teimef: {title : 'Mise en forme', items : fill_menu('mef')},
  			teiannot: {title : 'Annotation', items : fill_menu('annot')},
  			},
    valid_elements : add_valid_elements(), // Liste des balises TEI 
    protect: [
							/<math.*?>([\s\S]*?)<\/math>/g
            ],   
    forced_root_block : 'div',
    entity_encoding: 'raw',
    init_instance_callback: function (editor) {
      	editor.on('NodeChange', function (e) {
      		$('.mce-menu-item-normal').removeClass('tei-ok tei-ko');
      		$('.mce-menu-item-normal').show();
      	});   	 	
    	},    
    plugins: [
      'advlist autolink lists link image charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars code fullscreen',
      'insertdatetime media nonbreaking save contextmenu directionality',
      'emoticons template paste textcolor colorpicker textpattern imagetools codesample'
    ],
    toolbar1: 'undo redo | insert | gras italique souligne | pleft pcenter pright pjustify | linebreak | mathml | deltag',
    toolbar2: fill_toolbar(),
//     toolbar3: 'forecolor backcolor',
    image_advtab: true,
    content_css: [
      '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
      '//www.tinymce.com/css/codepen.min.css',
      $('#phpWebRoot').html() + '/plugins/Transcript/css/eman.css',
      $('#phpWebRoot').html() + '/plugins/Transcript/css/font-awesome/css/font-awesome.min.css'
    ],
    setup: function(editor) {
    	function manageButtons() {
    	  var btn = this;
    	  editor.on('NodeChange', function(e) {
//       	  console.log(e.element.nodeName + ' / ' + btn.settings.balise + ' / ' + tag_context[btn.settings.balise]);
    	    btn.disabled($.inArray(e.element.nodeName, tag_context[btn.settings.balise]) == -1);
//     	    btn.disabled(false); // temporaire == tout est légal
    	  });				 
    	}
    	editor.addButton("gras", 
    			{text: "",
    			 balise:'HI',
    			 icon: 'bold',
    			 onpostrender: manageButtons,			 
    			 onclick: function() {
    					var selection = tinymce.activeEditor.selection.getContent();
    	 				editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent("<hi rend='bold' class='bold' >" +  selection + "</hi>");
    	 				});
    			 	}
    			}
    		);	
    	editor.addButton("italique", 
    			{text: "",
    			 balise:'HI',
    			 icon: 'italic',
    			 onpostrender: manageButtons,
    			 onclick: function() {
    					var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
    	 				editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent("<hi rend='italic' class='italic' >" +  selection + "</hi>");
    	 				});
    			 	}
    			}
    		);	
    	editor.addButton("souligne", 
    			{text: "",
    			 balise:'HI',
    			 icon: 'underline',
    			 onpostrender: manageButtons,
    			 onclick: function() {
    					var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
    	 				editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent("<hi rend='underline' class='underline' >" +  selection + "</hi>");
    	 				});
    			 	}
    			}
    		);
    	editor.addButton("pleft", 
    			{text: "",
    			 balise:'P',
    			 icon: 'alignleft',
    			 onpostrender: manageButtons,
    			 onclick: function() {
    					var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
    	 				editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent("<p rend='left' class='left' >" +  selection + "</p>");
    	 				});
    			 	}
    			}
    		);
    	editor.addButton("pright", 
    			{text: "",
    			 balise:'P',
    			 icon: 'alignright',
    			 onpostrender: manageButtons,
    			 onclick: function() {
    					var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
    	 				editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent("<p rend='right' class='right' >" +  selection + "</p>");
    	 				});
    			 	}
    			}
    		);	
    	editor.addButton("pcenter", 
    			{text: "",
    			 balise:'P',
    			 icon: 'aligncenter',
    			 onpostrender: manageButtons,
    			 onclick: function() {
    					var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
    	 				editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent("<p rend='center' class='center' >" +  selection + "</p>");
    	 				});
    			 	}
    			}
    		);	
    	editor.addButton("pjustify", 
    			{text: "",
    			 balise:'P',
    			 icon: 'alignjustify',
    			 onpostrender: manageButtons,
    			 onclick: function() {
    					var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
    	 				editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent("<p rend='justify' class='justify' >" +  selection + "</p>");
    	 				});
    			 	}
    			}
    		);	    	
    	editor.addButton("linebreak", 
    			{text: "",
    		 	 balise:'LB',
    			 icon: '',
    			 image: tinymce.baseURL + '/emanbuttons/retourchariot.png',			 
    			 onpostrender: manageButtons,	
    			 onclick: function() {
    					var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
    	 				editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent("<i class='fa fa-fw fa-border green fa-caret-square-o-down'></i><lb />");
    	 				});
    			 	}
    			}
    		);		
    	editor.addButton("mathml", 
    			{text: "",
    		   balise: 'MATH',
    			 icon: '',
    			 onpostrender: manageButtons,
    			 image: tinymce.baseURL + '/emanbuttons/equation.png',
    			 onclick: function() {
    		      editor.windowManager.open({
    			        title: 'Code MathML',
    			        body: [
    			          {type: 'textbox', multiline: true, name: 'equation', label: 'Equation'}
    			        ],
    			        onsubmit: function(e) {
    				 				editor.undoManager.transact(function() {				
    				 					icon = "<i class='fa fa-fw fa-etsy fa-border green' title='Equation'>&nbsp;</i> ";
    				 					tinymce.activeEditor.selection.setContent(icon + "<math class='mathml' xmlns='http://www.w3.org/1998/Math/MathML'>" +  e.data[Object.keys(e.data)[0]] + "</math>");
    				 				});
    			        }				        
    		       });					
    			 	}
    			}
    		);
    	editor.addButton("deltag", 
    			{text: "",
    			 balise: 'DELTAG',
    			 icon: 'media',
    			 onpostrender: manageButtons,
    			 image: tinymce.baseURL + '/emanbuttons/trash.png',			 
    			 tooltip: 'Supprimer le tag conteneur',
    			 onclick: function() {
    				 node = tinymce.activeEditor.selection.getNode();
    				 tinymce.activeEditor.selection.select(node);
    				 var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
    	 			 editor.undoManager.transact(function() {				
    	 					tinymce.activeEditor.selection.setContent(selection);
    	 			 });				 					 
    			 	}
    			}
    		);	    	

      
    	var boutons = get_buttons_list();
    	$.each(boutons, function(i, button) {
    		add(editor, button);
    	});    	
    },
   });
  
  $('#toggle').click(function() {
  	pre = $('#preview img').detach();
  	ful = $('#transcript-image img').detach();
  	pre.appendTo('#transcript-image');
  	ful.appendTo('#preview');
  });
});

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

// Pour que le code des boutons soit valide pour TinyMCE
function add_valid_elements() {
	var valid_elements = 'i[class|title],math[xmlns],span[class],';
	buttons = get_buttons_list();
	$.each(buttons, function(i, button) {
		classes = "[class|title|data";
		if (typeof button.att != "undefined") {
  		classes = classes + "|" + button.att;
		}
		if (typeof button.att2 != "undefined") {
  		classes = classes + "|" + button.att2;
		}		
		valid_elements = valid_elements + button.cl.toLowerCase() + classes + "],";   
	});
	valid_elements = valid_elements + ',ttable,ffigure';
	return valid_elements;
}

// Ajout des boutons à la toolbar
function fill_toolbar() {
	var boutons = 'code | ';
  liste_boutons = get_buttons_list();
	$.each(liste_boutons, function(i, button) {	
		boutons = boutons + " " + button.cl;   
	});
	return boutons;
}

//Ajout des boutons au dropdown menu
function fill_menu(contexte) {  
	var liste_boutons = '';
	boutons = get_buttons_list();
	$.each(boutons, function(i, button) {
// 		console.log(button.cl + ' : ' + transcript_options[button.cl]);
		if (typeof transcript_options[button.cl] === "undefined") {
//   		console.log(button.cl);
  		return true;
		}  	
		if (button.menu == contexte) {
			liste_boutons = liste_boutons + " " + button.cl.toUpperCase();   	
		}
		if (typeof button.menu === "undefined" && contexte == 'struct') {
			liste_boutons = liste_boutons + " " + button.cl.toUpperCase();
		}
	})
	  
//   console.log(liste_boutons);
  return liste_boutons;
}

function activate_menu_items() {
	// Element type selected in TinyMCE
	element = tinymce.activeEditor.selection.getNode();	
	elementType = element.nodeName;
	// Tous les items visibles : ceux qui viennent d'apparaître suite au clic sur le menu
	menuItems = $('.mce-menu-item:visible span');
	$.each(menuItems, function(i, menuItem) {
  	menuName = $(menuItem).text();
		var balise = menuName.substr(0, menuName.indexOf(' ')); 
		var contexte = tag_context[balise]; 

// 		var contexte = 1; // temporaire == tout est légal
		if (typeof contexte != 'undefined') {
			// Remplacement des balises "spéciales" pour qu'elles soient un contexte valide
			i = contexte.indexOf('HEAD');
			contexte[i] = 'HEADD'; 	
			i = contexte.indexOf('TITLE');
			contexte[i] = 'TITTLE'; 			
			i = contexte.indexOf('FIGURE');
			contexte[i] = 'FFIGURE'; 	
			if ($.inArray(elementType, contexte) > -1 || balise == 'DIV' && elementType == 'BODY') { 
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

$('body').on('click', '.mce-tinymce > .mce-container-body .mce-btn', function() { 
	activate_menu_items();
});
$('body').on('mouseenter', '.mce-tinymce > .mce-container-body .mce-btn', function() { 
	activate_menu_items();
});

function add(editor, button) {
	// Menu par défaut
	if (typeof button.menu === "undefined") {
		var contexte = 'struct';
	} else {
		var contexte = button.menu;		
	}
	// Tags interdits : on remplace la balise temporaire par sa vraie désignation (headd => HEAD)
	if (button.cl == 'headd') {
		buttonName = 'HEAD (Entête)';			
	} else if (button.cl == 'tittle') {
		buttonName = 'TITLE (Titre)';			
	} else if (button.cl == 'ffigure') {
		buttonName = 'FIGURE (Illustration ou schéma)';			
	} else {		
		buttonName = button.cl.toUpperCase();		
	}	
	if (french_names[button.cl + '_fn'] != '') {
    french = ' (' + french_names[button.cl + '_fn'] + ')';  	
	} else {
  	french = '  ';
	}
	
  buttonName = buttonName + french;	
	editor.addMenuItem(button.cl.toUpperCase(), {
    text: buttonName,
    context: contexte,
    bouton: button, // Paramètre custom pour accès dans onClick 
    classes: button.cl,
    disabledStateSelector : ':not(a)',
		onclick: function(elem) {
			var selection = tinymce.activeEditor.selection.getContent({format: 'text'});
			bouton = this.settings.bouton;
			// Attributes, Forms, etc.
       editor.windowManager.open({
	        title: this.settings.bouton.cl.toUpperCase() + ' Attributes',
	        body: add_form_element(bouton),
	        onsubmit: function(e) {
//	        	console.log(e.data[Object.keys(e.data)[0]]);
//	        	console.log(e.data);
		 				editor.undoManager.transact(function() { 	
		 					attrValue = e.data[Object.keys(e.data)[0]];
		 					attrValue2 = e.data[Object.keys(e.data)[1]];
// 		 					attrValue3 = e.data[Object.keys(e.data)[2]];
		 					if (attrValue == "undefined") {
  		 					attrValue = '';
		 					}
		 					console.log(e.data);
		 					var attributes = "";
	 						tagValue = '';
	 						var couleur = null;
	 						$.each(e.data, function(name, value) {
  	 						console.log(name + ' / ' + value)
		 						if (name == 'tag') {
		 							tagValue = "<" + button.tag + ">" + value + "</" + button.tag + ">";
		 						} else {
  		 						if (name == 'couleur') {
    		 						couleur = value;
  		 						}
		 							if (value != 'none') {
				 						attributes = attributes + name + '="' + value + '" ';		 								 									 								
		 							}
		 						}		 						
		 					});
 	 						console.log(tagValue + '/' + attributes);		 					
		 					params = tinymce.activeEditor.windowManager.getParams();
		 					icon = button.icon;
              tagName = button.cl;
  		 				if (tagName == "table") {
  		 					tagName = "ttable";
		 					}
  		 				if (tagName == "rend") {
    		 				if (couleur != numm) {
      		 				
    		 				}
    		 			}
 		 					tinymce.activeEditor.selection.setContent("<" + tagName + " " + attributes + " class='" + attrValue + " " + tagName + "' data='" + visu[button.cl] + "'><i class='fa fa-fw fa-" + icon + " fa-border green inv'>&nbsp;</i> " + tagValue + selection + "</" + tagName + ">");				
		 				});
	        }				        
       } , {bouton: button, arg2: 'Hello world'} // Arguments pour la popup 
      );
		}
	});
  
};

// Given button, return corresponding form element
function add_form_element(button) {
/*
  console.log(button.att +  ' / ' + button.att2 + ' / ' + button.tag);
  console.log(button);
*/
var traductions = {"rend" : "rendu", "target" : "cible", "scribe" : "auteur", "type" : "type", "reason" : "raison", "cert" : "certitude", "place" : "emplacement", "rend" : "rendu", "when" : "quand", "break" : "break", "scribe" : "auteur", "xmllang" : "langue", "medium": "support"};
  		
	if (typeof button.values != "undefined") {
		if (button.values[button.values.length - 1].text != 'none') {
			button.values.push({text: 'none', value: 'none'});			
		}
		form_element = {type: 'listbox', name: button.att, label: traductions[button.att], values: button.values};
	} else if (typeof button.att != "undefined") {
		form_element = {type: 'textbox', name: button.att, label: traductions[button.att] };
		form_element['att'] = button.att;
 	} else {
 		form_element = null;
 	} 
  form_element2 =  '';
 	if (typeof button.att2 != "undefined") {
    // TODO : Textbox to type attributes' values 
    // TODO : condition + valeurs exactes
		form_element2 =  {type: 'textbox', name: button.att2, label: button.att2} ;
// console.log('Second attribut détecté.');
	}
	// Couleur
// 	console.log(button.values);
  if (button.att == 'rend') {
   	form_element3 = {type: 'listbox', name: 'couleur', label: 'Couleur', values: [ {text: 'rouge', value: 'red'}, {text: 'vert', value:'green'}]};    
  } else {
    form_element3 = '';
  }
// console.log(button);
	form = [form_element, form_element2, form_element3]; //  {type: 'textbox', name: 'title', label: 'Info bulle', value: button.ti } // title
	return form;
}

