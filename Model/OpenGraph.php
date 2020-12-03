<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 07/06/17
 * Time: 14:58
 */

namespace Lch\SeoBundle\Model;


class OpenGraph
{
    /**
     * @var string $title
     */
    private $title;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return OpenGraph
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var string $url
     */
    private $url;
    /**
     * @var string $image
     */
    private $image;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return OpenGraph
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return OpenGraph
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return OpenGraph
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return OpenGraph
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
}