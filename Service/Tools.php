<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/17
 * Time: 17:40
 */

namespace Lch\SeoBundle\Service;


use Doctrine\ORM\EntityManager;
use Lch\ComponentsBundle\Behaviour\Uuidable;
use Lch\SeoBundle\Behaviour\Seoable;
use Lch\SeoBundle\DependencyInjection\Configuration;
use Lch\SeoBundle\Event\GenerateSeoTagsEvent;
use Lch\SeoBundle\Event\GenerateSlugEvent;
use Lch\SeoBundle\Event\GetEntitiesCriteriasForSitemapEvent;
use Lch\SeoBundle\Exception\MissingSeoInterfaceException;
use Lch\SeoBundle\LchSeoBundleEvents;
use Lch\SeoBundle\Model\OpenGraph;
use Lch\SeoBundle\Model\SeoInterface;
use Lch\SeoBundle\Model\SeoTags;
use Lch\TranslateBundle\Exception\MissingTranslatableInterfaceException;
use Lch\TranslateBundle\Model\Interfaces\TranslatableInterface;
use Lch\TranslateBundle\Utils\LangSwitchHelper;
use Lch\TranslateBundle\Model\Behavior\Translatable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

// TODO split this in several single purposes classes (SOLID principles)
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
     * @var array $seoParameters
     */
    protected $seoParameters;

    /** @var string $schemeAndHttpHost */
    protected $schemeAndHttpHost;

    /** @var string $i18nStrategy */
    protected $i18nStrategy;

    /** @var string $defaultLocale */
    protected $defaultLocale;

    /**
     * Tools constructor.
     *
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param Router $router
     * @param array $seoParameters
     */
    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Router $router,
        LangSwitchHelper $languageSwithHelper,
        array $seoParameters,
        string $i18nStrategy,
        string $defaultLocale
    ) {
        $this->entityManager       = $entityManager;
        $this->eventDispatcher     = $eventDispatcher;
        $this->router              = $router;
        $this->seoParameters       = $seoParameters;
        $this->languageSwithHelper = $languageSwithHelper;
        $this->i18nStrategy        = $i18nStrategy;
        $this->defaultLocale       = $defaultLocale;

        $this->schemeAndHttpHost = "{$this->router->getContext()->getScheme()}://{$this->router->getContext()->getHost()}";

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }


    /**
     * @param $entityClass
     * @param $fields
     * @param $language
     *
     * @return string
     */
    public function generateSlug($entityClass, $fields, $entityId = null, $language = null)
    {

        // Get highest entity ID
        if ($entityId == null) {
            $lastEntity = $this->entityManager->getRepository($entityClass)->findOneBy([], ['id' => 'DESC']);
            $entityId   = is_object($lastEntity) ? $lastEntity->getId() : 1;
        } else {
            $lastEntity = $this->entityManager->getRepository($entityClass)->find($entityId);
        }

        // Send event to generate slug
        $generateEvent = new GenerateSlugEvent($entityClass, $entityId, $fields);
        $this->eventDispatcher->dispatch(
            $generateEvent,
            LchSeoBundleEvents::GENERATE_SLUG
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
        $params      = [
            'slug' => $slug
        ];

        if ($entity instanceof TranslatableInterface) {
            $params['language'] = $entity->getLanguage();
        }

        $existingEntity = $this->entityManager->getRepository($entityClass)->findOneBy($params);


        // No other entity found. This one is unique
        if ($existingEntity === null) {
            return true;
        }

        // Compare IDs differently if Uuid or not
        // TODO use class metadata checker to look for Uuidable trait
        $entityId         = $entity->getId();
        $existingEntityId = $existingEntity->getId();

        if ($entityId instanceof UuidInterface && $existingEntityId instanceof UuidInterface) {
            $entityIdComparison = ($entityId->toString() !== $existingEntityId->toString());
        } else {
            /** @var string $entityId */
            /** @var string $existingEntityId */
            $entityIdComparison = ($entityId !== $existingEntityId);
        }

        if (null !== $existingEntity &&
            $existingEntity instanceof $entityClass &&
            (null === $entity->getId() ||
             (null !== $entity->getId() &&
              $entityIdComparison))
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

        $openGraph = new OpenGraph();
        $seoTags   = new SeoTags();

        // Handle request, for specific pages not linked to entities
        if ($entityOrRequest instanceof Request) {
            $locale = $entityOrRequest->get('_locale');
            $seoTags->setRequest($entityOrRequest);
            $seoTags->setOpenGraph($openGraph);

            // Check if specific entry in config.yml yml match current route
            if (isset($this->seoParameters[$locale][Configuration::SPECIFIC][$entityOrRequest->get('_route')])) {
                $specificNodeTags = $this->seoParameters[$locale][Configuration::SPECIFIC][$entityOrRequest->get('_route')][Configuration::TAGS];
                $seoTags->setTitle($specificNodeTags[Configuration::TITLE]);
                $seoTags->setDescription($specificNodeTags[Configuration::DESCRIPTION]);
                $seoTags->setRoute($entityOrRequest->get('_route'));

                // TODO ensure or use route parameters ?
                try {
                    $seoTags->setCanonicalUrl($this->languageSwithHelper->getTranslatedUrl(
                        $entityOrRequest->get('_route'),
                        $entityOrRequest->get('_route_params')
                    )
                    );
                } catch (RouteNotFoundException $e) {
                    $seoTags->setCanonicalUrl($this->router->generate(
                        $entityOrRequest->get('_route'),
                        $entityOrRequest->get('_route_params'),
                        Router::ABSOLUTE_URL));
                }

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
                throw new MissingSeoInterfaceException('Given entity must implement SeoInterface interface and use Seoable');
            }

            if (! $entityOrRequest instanceof TranslatableInterface) {
                throw new MissingSeoInterfaceException('Given entity must implement TranslatableInterface');
            }

            $locale = $entityOrRequest->getLanguage();

            // Init objects
            $openGraph = $entityOrRequest->getOpenGraphData();

            // Tweak image URL to add scheme and host if necessary
            if (strpos($openGraph->getImage(), '/') === 0) {
                $openGraph->setImage($this->schemeAndHttpHost . $openGraph->getImage());
            }

            $seoTags->setTitle($entityOrRequest->getSeoTitle());
            $seoTags->setDescription($entityOrRequest->getSeoDescription());
            $seoTags->setRoute($entityOrRequest->getRouteName());

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
            $generateEvent,
            LchSeoBundleEvents::RENDER_SEO_TAGS
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
        if (! $entityInstance instanceof TranslatableInterface) {
            throw new MissingTranslatableInterfaceException('The entity ' . get_class($entityInstance) . ' must implement Translatable trait');
        }
        $routeParameters = [];
        foreach ($entityInstance->getRouteFields() as $routeParameter => $entityParameter) {
            $routeParameters[$routeParameter] = $this->propertyAccessor->getValue($entityInstance, $entityParameter);
        }
        $routeParameters['_locale'] = $entityInstance->getLanguage();

        return $this->languageSwithHelper->getTranslatedUrl($entityInstance->getRouteName(), $routeParameters);
    }

    /**
     * @return \SimpleXMLElement
     */
    public function generateSitemap(Request $request)
    {
        $locale  = $request->get('_locale');
        $sitemap = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><urlset />');

        // Define urlset
        $urlSet = $sitemap->addChild('urlset');
        $urlSet->addAttribute('xlmns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // Add specific items
        foreach ($this->seoParameters[$locale][Configuration::SPECIFIC] as $specific) {
            $specificNode = $specific[Configuration::SITEMAP];
            $specificUrl  = $urlSet->addChild('url');
            $specificUrl->addChild(Configuration::LOC, $this->schemeAndHttpHost . $specificNode[Configuration::LOC]);
            $specificUrl->addChild(Configuration::PRIORITY, $specificNode[Configuration::PRIORITY]);
        }

        // Loop on given entities for listing
        foreach ($this->seoParameters[$locale][Configuration::SITEMAP][Configuration::ENTITIES] as $entityClass => $data) {
            // Check given entity implements SeoInterface
            $reflection = new \ReflectionClass($entityClass);

            if (! $reflection->implementsInterface(SeoInterface::class)) {
                throw new MissingSeoInterfaceException("{$entityClass} does not implements SeoInterface and therefore cannot be added to sitemap");
            }
            if (! $reflection->implementsInterface(TranslatableInterface::class)) {
                throw new MissingSeoInterfaceException("{$entityClass} does not implements TranslatableInterface and therefore cannot be added to sitemap");
            }

            // Get all matching entities
            // Send event to customize filters for getting entities regarding to specific criterias
            $getEntitiesCriteriasEvent = new GetEntitiesCriteriasForSitemapEvent(
                $entityClass,
                ['language' => $locale]
            );
            $this->eventDispatcher->dispatch(
                $getEntitiesCriteriasEvent,
                LchSeoBundleEvents::GET_ENTITIES_CRITERIAS
            );
            // TODO enhance with custom generic repository?
            $entities = $this->entityManager->getRepository($entityClass)->findBy(
                $getEntitiesCriteriasEvent->getCriteriasArray()
            );


            $excludedEntities = $data[Configuration::ENTITIES_EXCLUDE] ?? [];
            // Loop on them and add them to urlset
            foreach ($entities as $entityInstance) {
                if (! in_array($entityInstance->getId(), $excludedEntities)) {
                    $routeParameters = [];

                    $url = $this->getUrl($entityInstance);

                    $urlNode = $urlSet->addChild('url');
                    $urlNode->addChild(Configuration::LOC, $url);
                    $urlNode->addChild(Configuration::PRIORITY,
                        $this->priorityCalculation($this->getUrl($entityInstance, Router::RELATIVE_PATH), $locale)
                    );
                }
            }
        }

        return $sitemap;
    }

    /**
     * @param string $url
     * @param string $locale
     *
     * @return float
     */
    public function priorityCalculation(string $url, string $locale)
    {
        $priority = 1.0;
        $urlParts = explode('/', $url);
        foreach ($urlParts as $part) {
            $priority -= floatval($this->seoParameters[$locale][Configuration::SITEMAP][Configuration::STEP]);
        }

        return $priority;
    }
}