<?php

namespace Lch\SeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class LchSeoExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Check langages
        // TODO find why primary config key is lost. Here is a clue https://symfony.com/doc/current/components/config/definition.html#array-node-options
        $rawConfig = $configs[0];

        // TODO find a way to use lch.translate.available_languages parameter
//        foreach($rawConfig as $language => $data) {
//            if(!in_array($language, $container->getParameter('lch.translate.available_languages'))) {
//                throw new InvalidConfigurationException("Langage {$language} is not registered as available language. See lch/translate-bundle configuration for more details.");
//            }
//            $container
//                ->setParameter(
//                    Configuration::ROOT_PARAMETERS_NAMESPACE . "." . $language . "." . Configuration::SPECIFIC,
//                    $rawConfig[$language][Configuration::SPECIFIC]
//                );
//
//            $container
//                ->setParameter(
//                    Configuration::ROOT_PARAMETERS_NAMESPACE . "." . $language . "." . Configuration::SPECIFIC,
//                    $rawConfig[$language][Configuration::SITEMAP]
//                );
//        }

        // Add parameters
        $container->setParameter(Configuration::ROOT_PARAMETERS_NAMESPACE . '.parameters', $rawConfig);
    }
}
