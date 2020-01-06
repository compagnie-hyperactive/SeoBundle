<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/17
 * Time: 17:33
 */

namespace Lch\SeoBundle\Event;


use Lch\SeoBundle\Model\SeoTags;
use Symfony\Contracts\EventDispatcher\Event;

class GenerateSeoTagsEvent extends Event
{
    /**
     * @var SeoTags
     */
    private $seoTags;
    /**
     * GenerateSeoTagsEvent constructor.
     * @param SeoTags $seoTags
     */
    public function __construct(SeoTags $seoTags) {
        $this->seoTags = $seoTags;
    }

    /**
     * @return SeoTags
     */
    public function getSeoTags()
    {
        return $this->seoTags;
    }

    /**
     * @param SeoTags $seoTags
     * @return GenerateSeoTagsEvent
     */
    public function setSeoTags($seoTags)
    {
        $this->seoTags = $seoTags;
        return $this;
    }
}