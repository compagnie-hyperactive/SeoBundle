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

    /**
     * @return array all fields neededfor slug generation
     */
    public function getSluggableFields();

    /**
     * @return array all fields needed to create a matching entity route, on a key => value basis
     * key is the route placeholder name, value the entity field linked
     */
    public function getRouteFields();


    /**
     * @return string the default title value to use if title not set on entity saving
     */
    public function getSeoTitleDefaultValue();
}