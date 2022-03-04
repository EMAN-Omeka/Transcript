# Transcript
TEI transcription for Omeka Classic content

# Installation

Une fois le [dépôt Git](https://github.com/ENS-ITEM/Transcript/) cloné dans le répertoire "plugins" de votre installation Omeka, copier le répertoire "teibp" à la racine de l'instance.

Sous Linux :

```
cd /racine/Omeka
cp -aR plugins/Transcript/teibp . 
```

Ce répertoire stockera les fichiers XML correspondant aux transcriptions (d'où la nécessité qu'il soit en dehors du répertoire "plugins" d'Omeka).

Vous pouvez ensuite activer le module de la façon habituelle.

Les balises TEI disponibles sont stockées dans le fichier `/resources/cm-tei-schema.xml`. La structure de ce fichier est très simple et devrait parler d'elle-même.

Voici le début du fichier :

```
<cm_tei_schema>
  ...
  <add place='top,bottom,margin,marginRight,marginLeft,opposite,overleaf,above,right,below,left,inline,inspace' type='' rend='' rendition='' medium='' source=''>
    <menuName>annot</menuName>
	  <ihmName>Ajout</ihmName>
    <children>handShift</children>
    <children>lb</children>
    <children>hi</children>
    ...

```

Ici on définit la balise `add` avec ses attributs `place` et la liste de ses valeurs contraintes, et les attributs `type`, `rend` etc. à valeurs libres.

`MenuName` définit le sous-menu dans lequel la balise apparaîtra dans l'éditeur (choix possibles : `annot` pour Annotation, `struct` pour Structure et `mef` pour Mise en forme).

`ìhmName` est le libellé de l'item de menu dans l'éditeur.

Les tags `children` en-dessous indiquent quelles balises sont autorisées à l'intérieur de la balise définie (donc `add` pourra contenir des balises `handShift`, `lb`, `hi`, etc).

Si le module [More User Roles](https://github.com/ebellempire/MoreUserRoles) est actif, les permissions d'accès aux différentes fonctions seront configurées en conséquence.

# Utilisation

Le plugin propose une page `/transcript/browse` qui est un navigateur dans les items et fichiers du site. 

Des listes permettent de naviguer vers n'importe quel fichier de l'instance Omeka.

Une barre d'information propose des liens vers le fichier Omeka original, le fichier XML contenant la transcription, la notice du fichier Omeka et la notice de l'item Omeka qui contient le fichier courant.

Il propose deux présentations différentes, selon que le visiteur est connecté ou non : visualisation ou édition.

# Rendu

Le rendu des transcriptions en HTML n'est pas géré directement par Transcript.

Sur EMAN, qui a financé ce projet, nous utilisons une instance [TEI Publisher](https://teipublisher.com), dont le rendu du fichier XML concerné apparaît dans un DIV sur la page du navigateur de transcriptions. 

L'instance TEI Publisher doit être correctement configurée et son URL renseignée sur la page `/transcript/admin/options`, ainsi que le nom du fichier `ODD`.

L'URL est de la forme `http://localhost:8080/exist/apps/tei-publisher/api/preview?wc=true&odd=EMAN.odd`, mais dépend évidemment de votre propre environnement de production. 

Notez que EMAN utilise la méthode `preview` de l'[API TEI Publisher](https://teipublisher.com/exist/apps/tei-publisher/api.html).

Il ne devrait pas être très difficile d'adapter le plugin pour qu'il s'interface avec un autre moteur de rendu. N'hésitez pas à nous contacter à ce sujet et/ou à nous proposer des alternatives.

# Éditeur

L'éditeur propose deux modes : [WYSIWYG](https://fr.wikipedia.org/wiki/What_you_see_is_what_you_get) ou Source.

En mode [WYSIWYG](https://fr.wikipedia.org/wiki/What_you_see_is_what_you_get), l'éditeur se comporte à peu près de la même façon qu'un [TinyMCE](https://www.tiny.cloud/) standard, à ceci près qu'un menu permet d'ajouter des tags [TEI](https://tei-c.org) au texte.

Des fenêtres popup facilitent la saisie des attributs des balises.

Le mode Source, donne accès à une instance CodeMirror permettant de travailler directement en XML, avec des fonctions d'autocomplétion, de suggestion contextuelle de tags et de fermeture automatique de ces mêmes tags.

Le code est coloré, signalant les erreurs de syntaxe les plus évidentes et mettant en valeur les différents éléments (balises, attributs, texte, etc.)

Un bouton `Valider` permet de vérifier que le code saisi est conforme à la spécification [TEI](https://tei-c.org).

# Glossaire

Dans l'onglet 'Options', vous pouvez saisir jusqu'à cinq valeurs, qui deviendront autant de champs à saisie libre pour les termes (voir plus bas).

Sous l'onglet 'Index', vous trouverez un lien 'Rafraîchir les index'.

Lors d'un clic sur ce lien, le plugin détectera automatiquement les tags `term` dans toutes les transcriptions sauvegardées, avec toutes leurs occurrences.

Vous pourrez alors saisir, pour chaque terme détecté, une définition et des valeurs pour les champs additionnels définis plus haut sur la page `Options`.

Toutes ces informations seront alors disponibles dans l'interface publique à l'url `/transcript/glossaire`, d'où vous pourrez naviguer sur la page de chaque terme. 

Un clic sur un terme mène à une page regroupant :

- la définition du terme
- les valeurs saisies pour les champs supplémentaires
- un tableau des occurrences, avec le contexte et des liens menant directement à l'occurrence en question, sur la page de rendu de la transcription

# Notes

Le schéma est géré par un fichier XML : `/ressources/cm-tei-schema.xml`.

La page `admin/transcript/list` propose une liste des fichiers transcrits ou en cours de transcription.

La page `admin/transcript/stats` indique des statistiques basiques sur l'avancement global de la transcription pour l'ensemble des fichiers et items du site.

# Credits

Ce module utilise une version modifiée de [TinyMCE 4](https://www.tiny.cloud/), ainsi qu'une intégration de [CodeMirror](https://codemirror.net/).

Du code a aussi été emprunté (et fortement modifié) au projet [XML4TEI](https://orazionelson.github.io/CodeMirrorXML4TEI/).

**Le début du développement de ce module a été réalisé avec le soutien de la bibliothèque Lettres de l'ENS.**

**La version 0.9 actuelle a été réalisée avec le soutien du projet Collex Amor (Université Paris-Saclay).**
