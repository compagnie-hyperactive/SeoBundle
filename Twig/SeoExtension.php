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
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getEntityClass', [$this, 'getEntityClass' ], [
                'needs_environment' => false,
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
}