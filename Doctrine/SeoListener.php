<?php

namespace Lch\SeoBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Lch\SeoBundle\Behaviour\Seoable;
use Lch\SeoBundle\Reflection\ClassAnalyzer;
use Lch\SeoBundle\Service\ToolsInterface;

class SeoListener implements EventSubscriber
{
	/**
	 * @var ToolsInterface
	 */
	private $tools;

	/**
	 * @var ClassAnalyzer
	 */
	private $classAnalyzer;

    public function __construct( ClassAnalyzer $classAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
    }

	/**
	 * @param ToolsInterface $tools
	 */
    public function setTools(ToolsInterface $tools) {
    	$this->tools = $tools;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->fillSeoEntity($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->fillSeoEntity($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function fillSeoEntity(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $classMetadata = new \ReflectionClass($entity);

        if (!$this->classAnalyzer->hasTrait($classMetadata, Seoable::class, true)){
            return;
        }

        $this->tools->seoFilling($entity);
    }
}
