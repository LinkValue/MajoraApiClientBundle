<?php

namespace Majora\HttpBundle\DependencyInjection;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
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
        $loader = new Loader\XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        if (!$container->hasDefinition('guzzle_wrapper')) {
            return;
        }

        // create services for each client register in app/config.yml
        foreach ($config['clients'] as $clientId => $clientConfig) {
            $this->createClient($container, $clientId, $clientConfig);
        }


        // Toolbar
        if ($container->getParameter('kernel.debug')) {
            $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('datacollector.xml');
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $clientId
     * @param array $clientConfig
     */
    public function createClient(ContainerBuilder $container, $clientId, array $clientConfig)
    {
        $container->setDefinition(sprintf('majora_http.handler.%s', $clientId), $container->getDefinition('guzzle.curl_handler'));
        $handlerStackReference = new Reference(sprintf('majora_http.handler.%s', $clientId));

        $container->getDefinition(sprintf('majora_http.handler.%s', $clientId));

        //Middleware
        $eventDispatcher = $container->getDefinition('majora.http_eventdispatcher');
        $eventDispatcher->replaceArgument(2, $clientId);
        $eventDispatcher->addMethodCall('push', [$handlerStackReference]);

        $clientConfig['handler'] = $handlerStackReference;
        $clientConfig['middleware'] = $eventDispatcher;

        $guzzleClient= $container->getDefinition('guzzle_wrapper');
        $guzzleClient->replaceArgument(0, $clientConfig);
        $container->setDefinition(sprintf('guzzle_http.%s', $clientId) , $guzzleClient);
    }
}
