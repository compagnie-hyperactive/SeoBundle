<?php

namespace Lch\SeoBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Lch\SeoBundle\Behaviour\Seoable;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SeoListener implements EventSubscriber
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->fillSeoEntity($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
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

        $classAnalyser = $this->container->get('lch.seo.reflection.class_analyzer');

        if (!$classAnalyser->hasTrait($classMetadata, Seoable::class, true)){
            return;
        }

        $tools = $this->container->get('lch.seo.tools');

        $tools->seoFilling($entity);

        $em = $args->getEntityManager();
        $em->flush();
    }
}
