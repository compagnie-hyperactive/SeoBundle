<?php

namespace Lch\SeoBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Lch\SeoBundle\Behaviour\Seoable;
use Lch\SeoBundle\Reflection\ClassAnalyzer;
use Lch\SeoBundle\Service\Tools;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SeoListener implements EventSubscriber
{
	/**
	 * @var Tools
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
	 * @param Tools $tools
	 */
    public function setTools(Tools $tools) {
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
