<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 07/06/17
 * Time: 14:58
 */

namespace Lch\SeoBundle\Model;


class SeoTags
{
    /**
     * @var string title
     */
    private $title;

    /**
     * @var string description
     */
    private $description;

    /**
     * @var string $canonicalUrl
     */
    private $canonicalUrl;

    /**
     * @var string $prev
     */
    private $prev;
    /**
     * @var string $next
     */
    private $next;
    /**
     * @var OpenGraph
     */
    private $openGraph;

    /**
     * @var SeoInterface
     */
    private $entity;
    /**
     * @var string
     */
    private $route;

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param string $route
     * @return SeoTags
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return SeoInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param SeoInterface $entity
     * @return SeoTags
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return SeoTags
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return SeoTags
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->canonicalUrl;
    }

    /**
     * @param string $canonicalUrl
     * @return SeoTags
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        $this->canonicalUrl = $canonicalUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * @param string $prev
     * @return SeoTags
     */
    public function setPrev($prev)
    {
        $this->prev = $prev;
        return $this;
    }

    /**
     * @return string
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param string $next
     * @return SeoTags
     */
    public function setNext($next)
    {
        $this->next = $next;
        return $this;
    }

    /**
     * @return OpenGraph
     */
    public function getOpenGraph()
    {
        return $this->openGraph;
    }

    /**
     * @param OpenGraph $openGraph
     * @return SeoTags
     */
    public function setOpenGraph($openGraph)
    {
        $this->openGraph = $openGraph;
        return $this;
    }
}