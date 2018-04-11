# Plugin mathjax pour tinymce 

Ce plugin pour l'éditeur **tinymce** (<http://www.tinymce.com/>) utilise **MathJax** (<http://www.mathjax.org/>) afin de permettre l'édition et l'affichage de formules mathématiques dans l'éditeur.
![plugin en fonctionnement](http://moodle.albert-thomas.org/file.php/1/demo.png)

## Démo : <http://tinymath.albert-thomas.org>

## Installation :

- Télécharger le dépôt, 
- dans le dossier `plugins` de tinymce, décompresser l'archive,
- renommer `mathjax` le dossier `efloti-plugin-mathjax-pour-tinymce...` obtenu,
- dans la page html contenant l'éditeur, ajouter `mathjax` à la ligne `plugins:`' du `tinymce.config({..., plugins: autres plugins,mathjax', ...});`

## Utilisation basique :

- Le raccourci clavier `ctrl+m` active/désactive les principales fonctionnalités du plugin,
- lorsqu'il est activé, l'appui sur la touche `$` insère une «zone de saisie»,
- on y tape une formule LaTeX qui est rendu au fur et à mesure de la saisie dans le coin supérieur droit de la fenêtre,
- une fois arrivé au résultat souhaité, il suffit de sortir de la «zone de saisie» pour que celle-ci soit remplacée par la formule correspondante,
- si on place le curseur (ou qu'on clique) sur la formule, la zone de saisie réapparait permettant ainsi de modifier/adapter la formule.
- **Astuces** :
    * complétion de commande LaTeX : `ctrl+ESPACE` (peut s'utiliser plusieurs fois successivement pour faire défiler les commandes)
    * dans une zone de saisie, l'appui sur la touche `$` fait passer du mode *inline* au mode *displaystyle* et vice versa.

## Mise en garde : 

- ce plugin est loin d'être rôdé ...
- il ne fonctionnera pas sous ie (en l'état, il fera planter tinymce sous ce navigateur :-(),
- il n'a été testé que sous firefox (3 ou +) et chrome.


