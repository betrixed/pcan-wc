<?php

use \Models\Assets;

class Front extends Controller {
        function show($f3, $args) {

        $view = $this->view;
        
        $view->metadata = array(
            "Description" => "Servicing the Greater Sydney area with a large range of mouth watering catering options is Julie’s Catering. We offer catering services for any event including christenings, funerals, birthdays and other functions.",
            "Keywords" => "Catering North Rocks – Funeral Catering North Rocks – Christening Catering North Rocks – Home Catering North Rocks – Caterer North Rocks – Catering Sydney – Funeral Catering Sydney – Christening Catering Sydney – Home Catering Sydney – Caterer Sydney"
        );
        
        $view->property = array (
            "og:title" => "Menus for you",
            "og:image" => "http://julies-catering.com.au/img/Mini_Christmas_Quiches.jpg",
            "og:image:type" => "image/jpeg",
            "og:site_name" => "Julie's Catering"
        );
        
        $view->title = "Julie's Catering";
        

        $view->assets(['bootstrap', 'custom']);
        
        $view->content = 'front/home.phtml';
        echo $view->render();
    }
}
