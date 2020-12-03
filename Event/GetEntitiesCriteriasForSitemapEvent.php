<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/17
 * Time: 17:33
 */

namespace Lch\SeoBundle\Event;


use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class GetEntitiesForSitemapEvent
 *
 * @package Lch\SeoBundle\Event
 */
class GetEntitiesCriteriasForSitemapEvent extends Event
{
    /**
     * @var string
     */
    protected $entityClass;
    /**
     * @var array
     */
    protected $criteriasArray;

    public function __construct(string $entityClass, array $criteriasArray)
    {
        $this->entityClass    = $entityClass;
        $this->criteriasArray = $criteriasArray;
    }

    /**
     * @return array
     */
    public function getCriteriasArray(): array
    {
        return $this->criteriasArray;
    }

    /**
     * @param array $criteriasArray
     *
     * @return GetEntitiesForSitemapEvent
     */
    public function setCriteriasArray(array $criteriasArray): GetEntitiesCriteriasForSitemapEvent
    {
        $this->criteriasArray = $criteriasArray;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     *
     * @return GenerateSlugEvent
     */
    public function setEntityClass(string $entityClass): GetEntitiesCriteriasForSitemapEvent
    {
        $this->entityClass = $entityClass;

        return $this;
    }
}