<?php

namespace Lch\SeoBundle\Doctrine;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Lch\SeoBundle\Behaviour\Seoable;
use Lch\SeoBundle\Reflection\ClassAnalyzer;
use Lch\SeoBundle\Service\Tools;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class SeoListener implements EventSubscriberInterface
{
	/**
	 * @var Tools
	 */
	private $seoTools;

	/**
	 * @var ClassAnalyzer
	 */
	private $classAnalyzer;

    public function __construct(ClassAnalyzer $classAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
    }

	/**
	 * @param Tools $tools
	 */
    public function setSeoTools(Tools $seoTools) {
    	$this->seoTools = $seoTools;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
//            Events::prePersist,
//            Events::preUpdate
            EasyAdminEvents::PRE_UPDATE => 'onPreUpdate',
            EasyAdminEvents::PRE_PERSIST => 'onPrePersist',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function onPrePersist(GenericEvent $event)
    {
        $this->fillSeoEntity($event);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function onPreUpdate(GenericEvent $event)
    {
        $this->fillSeoEntity($event);
    }



    /**
     * @param LifecycleEventArgs $args
     */
    private function fillSeoEntity(GenericEvent $args)
    {
        $entity = $args->getSubject();
        $classMetadata = new \ReflectionClass($entity);
        

        if (!$this->classAnalyzer->hasTrait($classMetadata, Seoable::class, true)){
            return;
        }

        $this->seoTools->seoFilling($entity);
    }
}
