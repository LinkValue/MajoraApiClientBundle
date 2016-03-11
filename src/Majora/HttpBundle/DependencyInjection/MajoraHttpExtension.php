<?php

namespace Majora\HttpBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class MajoraHttpExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');

        if (!$container->hasDefinition('guzzle_wrapper')) {
            return;
        }

        // create services for each client register in app/config.yml
        foreach ($config['clients'] as $clientId => $clientConfig) {
            $this->createClient($container, $clientId, $clientConfig);
        }


        $guzzleDefinition = $container->getDefinition('guzzle_wrapper');

        $guzzleDefinition->replaceArgument(0, $config);

        // Toolbar
        if ($container->getParameter('kernel.debug')) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('datacollector.yml');
        }

    }

    /**
     * @param ContainerBuilder $container
     * @param $clientId
     * @param array $clientConfig
     */
    public function createClient(ContainerBuilder $container, $clientId, array $clientConfig)
    {

        $guzzleClient= new Definition('%majora_http.custom_guzzle_client.class%');
        $guzzleClient->addArgument($clientConfig);

        $container->setDefinition('guzzle_http.'.$clientId , $guzzleClient);
    }
}
