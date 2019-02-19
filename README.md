# Transcript
TEI transcription for Omeka Classic content

# Installation

Une fois le dépôt Git cloné dans le répertoire "plugins" de votre installation Omeka, copier le répertoire teibp à la racine de l'instance.

Sous Linux :

```
cd /racine/Omeka
cp -aR plugins/Transcript/teibp . 
```

Ce répertoire stockera les fichiers XML correspondant aux transcriptions (d'où la nécessité qu'il soit en dehors du répertoire "plugins" d'Omeka).

Vous pouvez ensuite activer le module de la façon habituelle.

Si le module More User Roles est actif, les permissions d'accès aux différentes fonctions seront configurées en conséquence.

# Utilisation

Via votre navigateur, aller dans `admin/transcript`.

* Cliquer sur l'onglet *Tags disponibles*.
* Vous pouvez choisir les tags qui seront ensuite disponibles dans l'interface de transcription, ou simplement cocher la case *Activer tous les tags*.
* Vous pouvez aussi choisir d'afficher ou non les icônes et les commentaires sur les deux niveaux de lecture de la transcription.

Vous pouvez ensuite vous rendre sur la page de visualisation d'un fichier. Si vous avez les droits adéquats, vous y trouverez un bouton *Transcrire ce fichier* en haut à droite de la page. Un clic sur ce bouton vous mènera à l'interface de transcriptions

Une fois la transcription sauvegardée, elle est visible à l'adresse
/transcription/id-du-fichier.

# Notes

Ce module utilise une version modifiée de tinyMCE 4.

Les tags disponibles sont gérés par un fichier JSON (`javascript/buttons.json`). Il est possible d'en ajouter de nouveaux en respectant scrupuleusement la syntaxe.

De même, le fichier `javascript/controle.json` gère les contrôles de cohérence : dans quel contexte un tag peut-il être utilisé ? Ceci peut aussi être géré dans l'interface (`admin/transcript/controle`).

La page `admin/transcript/list` propose une liste des fichiers transcrits ou en cours de transcription.

La page `admin/transcript/stats` indique des statistiques basiques sur l'avancement global de la transcription pour l'ensemble des fichiers du site.

# Credits

**Module réalisé avec le soutien de la bibliothèque Lettres de l'ENS**