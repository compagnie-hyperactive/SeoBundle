<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 13/01/16
 * Time: 17:53
 */

namespace Lch\SeoBundle\Behaviour;

use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait Seoable
 * @package Lch\SeoBundle\Behaviour
 */
trait Seoable {

    /**
     * @var string
     * @ORM\Column(name="seo_slug", type="string", length=128, nullable=true, unique=true)
     */
    protected $slug;
    /**
     * @var string
     *
     * @ORM\Column(name="seo_title", type="string", length=128, nullable=true)
     * @Assert\Length(
     *      min = 1,
     *      max = 55,
     *      minMessage = "lch.seo.validations.seoTitle.minLenth ( {{limit}} )", payload = {"severity" = "warning"} ),
     *      maxMessage = "lch.seo.validations.seoTitle.maxLenth ( {{limit}} )", payload = {"severity" = "warning"} )
     * )
     */
    protected $seoTitle;
    /**
     * @var string
     *
     * @ORM\Column(name="seo_description", type="string", length=512, nullable=true)
     * @Assert\Length(
     *      min = 0,
     *      max = 160,
     *      minMessage = "lch.seo.validations.seoDescription.minLenth ( {{limit}} )", payload = {"severity" = "warning"}  ),
     *      maxMessage = "lch.seo.validations.seoDescription.maxLenth ( {{limit}} )", payload = {"severity" = "warning"}  )
     * )
     */
    protected $seoDescription;

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return Seoable
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param string $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * @param string $seoDescription
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;
    }
}