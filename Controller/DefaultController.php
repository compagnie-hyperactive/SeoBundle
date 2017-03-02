<?php

namespace Lch\SeoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LchSeoBundle:Default:index.html.twig');
    }
}
