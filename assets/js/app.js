/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

const $ = require('jquery');
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require('bootstrap');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');
// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

Routing.setRoutingData(routes);

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();

    function setHeight(){
        const height = $('body > .page-header').outerHeight();
        $('body > main').css('marginTop', height);
        $('.mt-100').css('margin-top', window.innerHeight+55);
    }

    setHeight();


    $( window ).resize(function() {
        setHeight();
    });

    $(window).scroll(function () {
        if ($(this).scrollTop() > 50) {
            $('body > header').css('backgroundColor', '#e88846');
            $('.big_logo').addClass('d-none');
            $('.little_logo').removeClass('d-none');

            $('.page-header .container-fluid').addClass('d-block');
            $('.page-header .container-fluid').removeClass( 'd-none');

        }
        if ($(this).scrollTop() < 50) {
            $('body > header').css('backgroundColor', 'transparent');
            $('.big_logo').removeClass('d-none');
            $('.little_logo').addClass('d-none');

            $('.page-header .container-fluid').addClass('d-none');
            $('.page-header .container-fluid').removeClass( 'd-block');
        }
    });

    $('.thumbnails img').hover( function() {
        $('.primary').attr('src', $(this).attr('src'));
    });

    $('.arrow-nav').click(function(){
        const arrow=$('.arrow-nav svg');
        if($('.container .mobile-menu').hasClass('d-none')){
            $('.mobile-menu').removeClass('d-none');
            $(arrow).removeClass('fa-bars');
            $(arrow).addClass('fa-times');
        }
        else{
            $('.mobile-menu').addClass('d-none');
            $(arrow).addClass('fa-bars');
            $(arrow).removeClass('fa-times');
        }
    });

    $(".alert-fade").click(function(){
        $(this).css('display', 'none');
    })

    $(window).scroll(function(){
        const message = $(".alert-fade");
        $(message).css("opacity", 1 - $(window).scrollTop() / 150);
        if(1-$(window).scrollTop()/150 <= 0){
            $(message).css("display", 'none');
        }
    });

    const imgs=document.getElementsByClassName('resize-img');


    imgs.forEach(function(element, index){
        const width = (element.width * 350)/element.height;
        element.width=width;
        element.height=350;
        const effects=$('.effect')
        effects[index].style.height=10;
        effects[index].style.width=10;

        console.log(effects[index]);
    });
    $(".loader").fadeOut("slow");

    $('.cookie-notice .accept').click(function() {
        $.post(
            Routing.generate('cookiesChoice'),
            {
                accept : true,
            },
            removeCookiesNotice,
            'json'
        );

        function removeCookiesNotice(){
            $('.cookie-notice').addClass('d-none');
        }
    });

    $('.cookie-notice .refuse').click(function() {
        $.post(
            Routing.generate('cookiesChoice'),
            {
                accept : false,
            },
            removeCookiesNotice,
            'json'
        );

        function removeCookiesNotice(){
            $('.cookie-notice').addClass('d-none');
        }
    });

    $( ".downloadFile").click(function(event){
        event.preventDefault();
        $(this).next().next( "#begin" ).removeClass("d-none");
        window.location.href = this.href;
    });

    $(".modal").on('hidden.bs.modal', function (e) {
        $(this).find('iframe').attr("src", $(this).find('iframe').attr("src"));
    });
});


// any CSS you require will output into a single css file (app.scss in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
