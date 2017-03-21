<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/17
 * Time: 17:33
 */

namespace Lch\SeoBundle\Event;


use Symfony\Component\EventDispatcher\Event;

class GenerateSlugEvent extends Event
{
    /**
     * @var string
     */
    private $entityClass;
    /**
     * @var int the highest entity ID for slug generation
     */
    private $higestEntityId;


    /**
     * @var array
     */
    private $fields;

    /**
     * @var string
     */
    private $slug;

    /**
     * GenerateSlugEvent constructor.
     * @param string $entityClass
     * @param array $fields
     * @param int $highestEntityId
     */
    public function __construct($entityClass, $highestEntityId, array $fields) {
        $this->entityClass = $entityClass;
        $this->higestEntityId = $highestEntityId;
        $this->fields = $fields;
        $this->slug = "";
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     * @return GenerateSlugEvent
     */
    public function setFields(array $fields): GenerateSlugEvent
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return int
     */
    public function getHigestEntityId(): int
    {
        return $this->higestEntityId;
    }

    /**
     * @param int $higestEntityId
     * @return GenerateSlugEvent
     */
    public function setHigestEntityId(int $higestEntityId): GenerateSlugEvent
    {
        $this->higestEntityId = $higestEntityId;
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
     * @return GenerateSlugEvent
     */
    public function setEntityClass(string $entityClass): GenerateSlugEvent
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return GenerateSlugEvent
     */
    public function setSlug(string $slug): GenerateSlugEvent
    {
        $this->slug = $slug;
        return $this;
    }
}