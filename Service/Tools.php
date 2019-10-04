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
use Lch\SeoBundle\DependencyInjection\Configuration;
use Lch\SeoBundle\Event\GenerateSeoTagsEvent;
use Lch\SeoBundle\Event\GenerateSlugEvent;
use Lch\SeoBundle\Exception\MissingSeoInterfaceException;
use Lch\SeoBundle\LchSeoBundleEvents;
use Lch\SeoBundle\Model\OpenGraph;
use Lch\SeoBundle\Model\SeoInterface;
use Lch\SeoBundle\Model\SeoTags;
use Lch\TranslateBundle\Exception\MissingTranslatableInterfaceException;
use Lch\TranslateBundle\Model\Interfaces\TranslatableInterface;
use Lch\TranslateBundle\Utils\LangSwitchHelper;
use Lch\TranslateBundle\Model\Behavior\Translatable;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Tools
{
    const DEFAULT_DELIMITER = '-';
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;


    /**
     * @var PropertyAccess
     */
    protected $propertyAccessor;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var LangSwitchHelper
     */
    protected $languageSwithHelper;

    /**
     * @var array
     */
    protected $sitemapParameters;

    protected $schemeAndHttpHost;

    /**
     * Tools constructor.
     *
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param Router $router
     * @param array $sitemapParameters
     */
    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Router $router,
        LangSwitchHelper $languageSwithHelper,
        array $sitemapParameters
    ) {
        $this->entityManager       = $entityManager;
        $this->eventDispatcher     = $eventDispatcher;
        $this->router              = $router;
        $this->sitemapParameters   = $sitemapParameters;
        $this->languageSwithHelper = $languageSwithHelper;

        $this->schemeAndHttpHost = "{$this->router->getContext()->getScheme()}://{$this->router->getContext()->getHost()}";

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }


    /**
     * @param $entityClass
     * @param $fields
     *
     * @return string
     */
    public function generateSlug($entityClass, $fields)
    {

        // Get highest entity ID
        $lastEntity   = $this->entityManager->getRepository($entityClass)->findOneBy([], ['id' => 'DESC']);
        $lastEntityId = is_object($lastEntity) ? $lastEntity->getId() : 1;

        // Send event to generate slug
        $generateEvent = new GenerateSlugEvent($entityClass, $lastEntityId, $fields);
        $this->eventDispatcher->dispatch(
            LchSeoBundleEvents::GENERATE_SLUG,
            $generateEvent
        );

        if ($generateEvent->getSlug() !== "") {
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
     *
     * @return bool
     */
    public function isSlugUnique($entity, $slug)
    {
        $entityClass = get_class($entity);

        $existingEntity = $this->entityManager->getRepository($entityClass)->findOneBy(['slug' => $slug]);
        if (null !== $existingEntity &&
            $existingEntity instanceof $entityClass &&
            (null === $entity->getId() ||
             (null !== $entity->getId() &&
              $existingEntity->getId() !== $entity->getId()))
        ) {
            return false;
        }

        return true;
    }


    /**
     * @param $entity
     */
    public function seoFilling($entity)
    {
        // TODO check Seoable recursively
        if (! in_array(Seoable::class, class_uses($entity))
            && ! $entity instanceof SeoInterface) {
            throw new Exception();
        }

        // Fill title
        if (empty($entity->getSeoTitle())) {
            $entity->setSeoTitle($entity->getSeoTitleDefaultValue());
        }

        // Fill slug if empty
        if (empty($entity->getSlug())) {
            // Fill required fields for SEO generation
            $fields = [];
            foreach ($entity->getSluggableFields() as $field) {
                $fields[$field] = $this->propertyAccessor->getValue($entity, $field);
            }
            $entity->setSlug($this->generateSlug(get_class($entity), $fields));
        }

        $currentSlug = $entity->getSlug();

        // Recursive slug check with suffix addition
        while (! $this->isSlugUnique($entity, $currentSlug)) {
            $currentSlugParts = explode(static::DEFAULT_DELIMITER, $currentSlug);
            if (intval($number = $currentSlugParts[count($currentSlugParts) - 1])) {
                array_pop($currentSlugParts);
                $number++;
                $currentSlug = implode(static::DEFAULT_DELIMITER,
                        $currentSlugParts) . static::DEFAULT_DELIMITER . "{$number}";
            } else {
                $currentSlug = "{$currentSlug}-2";
            }
        }
        $entity->setSlug($currentSlug);
    }

    /**
     * @param $stringToSanitize
     * @param string $delimiter
     *
     * @return string
     */
    public function sanitize($stringToSanitize, $delimiter = self::DEFAULT_DELIMITER)
    {

        $stringToSanitize = htmlentities($stringToSanitize, ENT_NOQUOTES, 'UTF-8');

        $stringToSanitize = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#',
            '\1', $stringToSanitize);
        $stringToSanitize = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1',
            $stringToSanitize); // pour les ligatures e.g. '&oelig;'
        $stringToSanitize = preg_replace('#&[^;]+;#', '', $stringToSanitize); // supprime les autres caractères

        $stringToSanitize = strtolower(trim(preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $stringToSanitize), $delimiter));
        $slug             = preg_replace("/[\/_|+ -]+/", $delimiter, $stringToSanitize);

        return $slug;
    }

    /**
     * @param mixed|null $entityOrRequest
     *
     * @return SeoTags
     */
    public function generateTags($entityOrRequest)
    {

        // Handle request, for specific pages not linked to entities
        if ($entityOrRequest instanceof Request) {
            $openGraph = new OpenGraph();
            $seoTags   = new SeoTags();
            $seoTags->setRequest($entityOrRequest);
            $seoTags->setOpenGraph($openGraph);

            // Check if specific entry in config.yml yml match current route
            if (isset($this->sitemapParameters[Configuration::SPECIFIC][$entityOrRequest->get('_route')])) {
                $specificNodeTags = $this->sitemapParameters[Configuration::SPECIFIC][$entityOrRequest->get('_route')][Configuration::TAGS];
                $seoTags->setTitle($specificNodeTags[Configuration::TITLE]);
                $seoTags->setDescription($specificNodeTags[Configuration::DESCRIPTION]);
                $seoTags->setRoute($entityOrRequest->get('_route'));

                // TODO ensure or use route parameters ?
                $seoTags->setCanonicalUrl($this->router->generate($entityOrRequest->get('_route'), [],
                    Router::ABSOLUTE_URL));

                // Open Graph
                $openGraph->setTitle($seoTags->getTitle());
                $openGraph->setDescription($seoTags->getDescription());
                $openGraph->setUrl($seoTags->getCanonicalUrl());
            }

            return $seoTags;
        } // Handle entity
        else {
            if (! $entityOrRequest instanceof SeoInterface || ! in_array(Seoable::class,
                    class_uses($entityOrRequest))) {
                throw new MissingSeoInterfaceException('Given entity must implement SeoInterface class and use Seoable');
            }

            // Init objects
            $openGraph = $entityOrRequest->getOpenGraphData();

            // Tweak image URL to add scheme and host if necessary
            if (strpos($openGraph->getImage(), '/') === 0) {
                $openGraph->setImage($this->schemeAndHttpHost . $openGraph->getImage());
            }

            // Add route
            $openGraph->setUrl($this->getUrl($entityOrRequest));

            $seoTags = new SeoTags();
            $seoTags->setOpenGraph($openGraph);
            $seoTags->setEntity($entityOrRequest);
            $seoTags->setCanonicalUrl($openGraph->getUrl());

            $seoTags->setTitle($entityOrRequest->getSeoTitle());
            $seoTags->setDescription($entityOrRequest->getSeoDescription());
        }

        // Send event to generate tags
        $generateEvent = new GenerateSeoTagsEvent($seoTags);
        $this->eventDispatcher->dispatch(
            LchSeoBundleEvents::RENDER_SEO_TAGS,
            $generateEvent
        );

        return $generateEvent->getSeoTags();
    }

    /**
     * @param SeoInterface $entityInstance
     * @param string $routeType
     *
     * @return string
     */
    public function getUrl(SeoInterface $entityInstance, string $routeType = Router::ABSOLUTE_URL)
    {
        if(!$entityInstance instanceof TranslatableInterface) {
            throw new MissingTranslatableInterfaceException('The entity' . get_class($entityInstance) . 'must implement Translatable trait');
        }
        $routeParameters = [];
        foreach ($entityInstance->getRouteFields() as $routeParameter => $entityParameter) {
            $routeParameters[$routeParameter] = $this->propertyAccessor->getValue($entityInstance, $entityParameter);
        }
        $routeParameters['_locale'] = $entityInstance->getLanguage();

        return $this->languageSwithHelper->getTranslatedPath($entityInstance->getRouteName(), $routeParameters);
    }

    /**
     * @return \SimpleXMLElement
     */
    public function generateSitemap()
    {
        $sitemap = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><urlset />');

        // Define urlset
        $urlSet = $sitemap->addChild('urlset');
        $urlSet->addAttribute('xlmns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // Add specific items
        foreach ($this->sitemapParameters[Configuration::SPECIFIC] as $specific) {
            $specificNode = $specific[Configuration::SITEMAP];
            $specificUrl  = $urlSet->addChild('url');
            $specificUrl->addChild(Configuration::LOC, $this->schemeAndHttpHost . $specificNode[Configuration::LOC]);
            $specificUrl->addChild(Configuration::PRIORITY, $specificNode[Configuration::PRIORITY]);
        }

        // Loop on given entities for listing
        foreach ($this->sitemapParameters[Configuration::SITEMAP][Configuration::ENTITIES] as $entityClass => $data) {
            // Check given entity implements SeoInterface
            $reflection = new \ReflectionClass($entityClass);

            if (! $reflection->implementsInterface(SeoInterface::class)) {
                throw new MissingSeoInterfaceException("{$entityClass} does not implements SeoInterface and therefore cannot be added to sitemap");
            }

            // Get all entities
            // TODO enhance with custom generic repository?
            $entities = $this->entityManager->getRepository($entityClass)->findAll();

            // Loop on them and add them to urlset
            foreach ($entities as $entityInstance) {
                if (! in_array($entityInstance->getId(), $data[Configuration::ENTITIES_EXCLUDE])) {
                    $routeParameters = [];

                    $url = $this->getUrl($entityInstance);

                    $urlNode = $urlSet->addChild('url');
                    $urlNode->addChild(Configuration::LOC, $url);
                    $urlNode->addChild(Configuration::PRIORITY,
                        $this->priorityCalculation($this->getUrl($entityInstance, Router::RELATIVE_PATH)));
                }
            }
        }

        return $sitemap;
    }

    /**
     * @param string $url
     *
     * @return float
     */
    public function priorityCalculation(string $url)
    {
        $priority = 1.0;
        $urlParts = explode('/', $url);
        foreach ($urlParts as $part) {
            $priority -= floatval($this->sitemapParameters[Configuration::SITEMAP][Configuration::STEP]);
        }

        return $priority;
    }
}