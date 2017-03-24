<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/17
 * Time: 17:40
 */

namespace Lch\SeoBundle\Service;


use Doctrine\ORM\EntityManager;
use Lch\SeoBundle\Behaviour\Seoable;
use Lch\SeoBundle\Event\GenerateSlugEvent;
use Lch\SeoBundle\LchSeoBundleEvents;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Tools
{
    const DEFAULT_DELIMITER = '-';
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    private $eventDispatcher;


    /**
     * @var PropertyAccess
     */
    private $propertyAccessor;

    /**
     * Tools constructor.
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }


    /**
     * @param $entityClass
     * @param $fields
     * @return string
     */
    public function generateSlug($entityClass, $fields) {

        // Get highest entity ID
        $lastEntity = $this->entityManager->getRepository($entityClass)->findOneBy([], ['id' => 'DESC']);
        $lastEntityId = is_object($lastEntity) ? $lastEntity->getId() : 1;

        // Send event to generate slug
        $generateEvent = new GenerateSlugEvent($entityClass, $lastEntityId, $fields);
        $this->eventDispatcher->dispatch(
            LchSeoBundleEvents::GENERATE_SLUG,
            $generateEvent
        );

        if($generateEvent->getSlug() !== "") {
            $slug = $generateEvent->getSlug();
        } else {
            $slug = "";
            // sanitize each given field, in array order
            // This assume there is a unique index on slug fields collection, or slug filed if only one
            foreach ($fields as $field) {
                $slug .= $this->sanitize($field) . static::DEFAULT_DELIMITER;
            }
            // Remove last delimiter
            $slug = rtrim($slug, static::DEFAULT_DELIMITER);
        }

        return $slug;
    }

    /**
     * @param object $entity
     * @param $slug
     * @return bool
     */
    public function isSlugUnique($entity, $slug) {
        $existingEntity = $this->entityManager->getRepository(get_class($entity))->findOneBy(['slug' => $slug]);
        if($existingEntity !== null && $existingEntity !== $entity) {
            return false;
        }
        return true;
    }


    /**
     * @param $entity
     */
    public function seoFilling($entity) {
        // TODO check Seoable recursively and check SeoInterface implementation
        if(!in_array(Seoable::class, class_uses($entity))) {
            throw new Exception();
        }

        // Fill title
        if(empty($entity->getSeoTitle())) {
            $entity->setSeoTitle($entity->getSeoTitleDefaultValue());
        }

        // Fill slug if empty
        if(empty($entity->getSlug())) {
            // Fill required fields for SEO generation
            $fields = [];
            foreach($entity->getSluggableFields() as $field) {
                $fields[$field] = $this->propertyAccessor->getValue($entity, $field);
            }
            $entity->setSlug($this->generateSlug(get_class($entity), $fields));
        }

        if(!$this->isSlugUnique($entity, $entity->getSlug())) {
            throw new Exception("Slug exists");
        }
    }

    /**
     * @param $stringToSanitize
     * @param string $delimiter
     * @return string
     */
    public function sanitize($stringToSanitize, $delimiter = self::DEFAULT_DELIMITER) {

        $stringToSanitize = htmlentities($stringToSanitize, ENT_NOQUOTES, 'UTF-8');

        $stringToSanitize = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $stringToSanitize);
        $stringToSanitize = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $stringToSanitize); // pour les ligatures e.g. '&oelig;'
        $stringToSanitize = preg_replace('#&[^;]+;#', '', $stringToSanitize); // supprime les autres caractères

        $stringToSanitize = strtolower( trim( preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $stringToSanitize ), $delimiter ) );
        $slug = preg_replace("/[\/_|+ -]+/", $delimiter, $stringToSanitize);
        return $slug;

    }
}