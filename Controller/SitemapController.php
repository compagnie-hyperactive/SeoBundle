<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/06/17
 * Time: 17:18
 */

namespace Lch\SeoBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{

    public function generateSitemap(Request $request) {
        return new Response(
            $this->get('lch.seo.tools')
                 ->generateSitemap($request)
                 ->asXML()
        );
    }
}