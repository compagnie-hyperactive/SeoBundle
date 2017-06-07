<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 21/03/17
 * Time: 09:58
 */

namespace Lch\SeoBundle\Model;


interface SeoInterface
{
    const OG_TITLE = 'title';
    const OG_TYPE = 'type';
    const OG_URL = 'url';
    const OG_IMAGE = 'image';

    /**
     * @return array all fields needed for slug generation
     */
    public function getSluggableFields();

    /**
     * @return string the route name to generate detail page URL
     */
    public function getRouteName();
    /**
     * @return array all fields needed to create a matching entity route, on a key => value basis
     * key is the route placeholder name, value the entity field linked
     */
    public function getRouteFields();


    /**
     * @return string the default title value to use if title not set on entity saving
     */
    public function getSeoTitleDefaultValue();

    /**
     * @return OpenGraph $openGraph
     */
    public function getOpenGraphData();
}