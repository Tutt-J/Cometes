/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.scss in this case)
require('../css/admin.css');
require('../js/adminScripts.js');

$(document).ready(function() {
        bsCustomFileInput.init()
        // On récupère la balise <div> en question qui contient l'attribut « data-prototype » qui nous intéresse.
        var $container = $('div#event_eventPricings');


        // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
        var index = $container.find('fieldset').length;

        // On ajoute un nouveau champ à chaque clic sur le lien d'ajout.
        $('#add_pricing').click(function(e) {
            addPricing($container);

            e.preventDefault(); // évite qu'un # apparaisse dans l'URL
            return false;
        });

        // On ajoute un premier champ automatiquement s'il n'en existe pas déjà un (cas d'une nouvelle annonce par exemple).
        if (index !== 0) {
            // S'il existe déjà des catégories, on ajoute un lien de suppression pour chacune d'entre elles
            $container.children('fieldset').each(function(loop_index) {
                $(this).children('legend').text('Tarif n°' + (loop_index+1))
                addDeleteLink($(this));
            });
        }

        // La fonction qui ajoute un formulaire CategoryType
        function addPricing(container) {
            // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
            var number_field = $container.find('fieldset').length;
            alert(number_field);
            // Dans le contenu de l'attribut « data-prototype », on remplace :
            // - le texte "__name__label__" qu'il contient par le label du champ
            // - le texte "__name__" qu'il contient par le numéro du champ
            var template = container.attr('data-prototype')
                .replace(/__name__label__/g, 'Tarif n°' + (number_field+1))
                .replace(/__name__/g,        number_field)
            ;

            // On crée un objet jquery qui contient ce template
            var $prototype = $(template);

            // On ajoute au prototype un lien pour pouvoir supprimer la catégorie
            addDeleteLink($prototype);

            // On ajoute le prototype modifié à la fin de la balise <div>
            $container.append($prototype);
        }

        // La fonction qui ajoute un lien de suppression d'une catégorie
        function addDeleteLink($prototype) {
            // Création du lien
            var $deleteLink = $('<a href="#" class="btn btn-danger">Supprimer</a>');

            // Ajout du lien
            $prototype.append($deleteLink);

            // Ajout du listener sur le clic du lien pour effectivement supprimer la catégorie
            $deleteLink.click(function(e) {
                $prototype.remove();
                $container.children('fieldset').each(function(delete_loop_index) {
                    $(this).children('legend').text('Tarif n°' + (delete_loop_index+1))
                });
                e.preventDefault(); // évite qu'un # apparaisse dans l'URL
                return false;
            });
        }

    $(document).on('keyup', '.automatic_date', function(){
        if ($(this).val().length == 2 || $(this).val().length == 5){
            $(this).val($(this).val() + "/");
        }

        if ($(this).val().length == 10){
            $(this).val($(this).val() + " ");
        }
        if ($(this).val().length == 13){
            $(this).val($(this).val() + ":");
        }
    });

    $('.custom-file-label').html('Choisissez un fichier');

    $('.custom-file-input').on('change', function(event) {
        var inputFile = event.currentTarget;
        $(inputFile).parent()
            .find('.custom-file-label')
            .html(inputFile.files[0].name);
    });
});

$('p[data-target="#deleteModal"]').click(function(event) {
    event.stopPropagation();
    var id = $(this).data('id');
    var title = $(this).data('title');
    $("#deleteModal .modal-footer .idValue").val(id);
    $("#deleteModal .modal-body .titleValue").text(title);
    $('#deleteModal').modal('toggle');
});

$('p[data-target="#cancelModal"]').click(function(event) {
    event.stopPropagation();
    var id = $(this).data('id');
    var title = $(this).data('title');
    $("#cancelModal .modal-footer .idValue").val(id);
    $("#cancelModal .modal-body .titleValue").text(title);
    $('#cancelModal').modal('toggle');
});
// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
