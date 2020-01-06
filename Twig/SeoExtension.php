<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/17
 * Time: 16:54
 */

namespace Lch\SeoBundle\Twig;


use Lch\SeoBundle\Service\Tools;
use Symfony\Component\Config\Definition\Exception\Exception;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    /**
     * @var Environment $twig
     */
    private $twig;

    /**
     * @var Tools
     */
    private $seoTools;

    public function __construct(Environment $twig, Tools $seoTools) {
        $this->twig = $twig;
        $this->seoTools = $seoTools;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getEntityClass', [$this, 'getEntityClass' ], [
                'needs_environment' => false,
            ]),
            new TwigFunction('renderSeoTags', [$this, 'renderSeoTags' ], [
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


    /**
     * @param mixed $entityOrRequest
     * @return string
     */
    public function renderSeoTags($entityOrRequest) {
        $seoTags = $this->seoTools->generateTags($entityOrRequest);
        return $this->twig->render('@LchSeo/Front/seo.html.twig', [
            'seoTags' => $seoTags
        ]);
    }
}