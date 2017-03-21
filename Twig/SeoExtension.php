<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/17
 * Time: 16:54
 */

namespace Lch\SeoBundle\Twig;


use Symfony\Component\Config\Definition\Exception\Exception;

class SeoExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment $twig
     */
    private $twig;

    public function __construct(\Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getEntityClass', [$this, 'getEntityClass' ], [
                'needs_environment' => false,
            ]),
            new \Twig_SimpleFunction('renderSeoTags', [$this, 'renderSeoTags' ], [
                'needs_environment' => false,
                'is_safe' => array('html')
            ]),
        );
    }

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityClass($entity) {
        if(!is_object($entity)) {
            // TODO specialize
            throw new Exception();
        }
        return (new \ReflectionClass($entity))->getName();
    }


    public function renderSeoTags($entity = null) {
        // TODO check entity is object and get Seoable trait
        // TODO handle option for site title
        return $this->twig->render('@LchSeo/Front/seo.html.twig', [
            'entity' => $entity
        ]);
    }
}