<?php

namespace Lch\SeoBundle\Service;

use Lch\SeoBundle\Model\SeoTags;
use Lch\SeoBundle\Model\SeoInterface;
use Symfony\Component\HttpFoundation\Request;

interface ToolsInterface
{
    const DEFAULT_DELIMITER = '-';

    public function generateSlug($entityClass, $fields, $entityId, $language);

    public function isSlugUnique($entity, $slug);

    public function seoFilling($entity);

    public function sanitize($stringToSanitize, $delimiter = self::DEFAULT_DELIMITER);

    public function generateTags($entityOrRequest);

    public function getUrl(SeoInterface $entityInstance, string $routeType = Router::ABSOLUTE_URL);

    public function generateSitemap(Request $request);

    public function priorityCalculation(string $url, string $locale);
}