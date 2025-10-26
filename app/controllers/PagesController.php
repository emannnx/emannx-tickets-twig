<?php
namespace App\Controllers;

class PagesController {
    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function landing() {
        echo $this->twig->render('pages/landing.html.twig');
    }
}