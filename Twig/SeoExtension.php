<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/17
 * Time: 16:54
 */

namespace Lch\SeoBundle\Twig;


use Lch\SeoBundle\Service\ToolsInterface;
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
     * @var ToolsInterface
     */
    private $seoTools;

    public function __construct(Environment $twig, ToolsInterface $seoTools) {
        $this->twig = $twig;
        $this->seoTools = $seoTools;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('get_entity_class', [$this, 'getEntityClass' ], [
                'needs_environment' => false,
            ]),
            new TwigFunction('render_seo_tags', [$this, 'renderSeoTags' ], [
                'needs_environment' => false,
                'is_safe' => array('html')
            ]),
        );
    }

    /**
     * @param object $entity
     * @return string
     * @throws \ReflectionException
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
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderSeoTags($entityOrRequest) {
        $seoTags = $this->seoTools->generateTags($entityOrRequest);
        return $this->twig->render('@LchSeo/Front/seo.html.twig', [
            'seoTags' => $seoTags
        ]);
    }
}