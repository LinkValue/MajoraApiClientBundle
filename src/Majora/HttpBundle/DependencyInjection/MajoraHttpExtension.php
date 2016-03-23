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
        $loader->load('services.xml'); //xml

        if (!$container->hasDefinition('guzzle_wrapper')) {
            return;
        }

        // create services for each client register in app/config.yml
        foreach ($config['clients'] as $clientId => $clientConfig) {
            $this->createClient($container, $clientId, $clientConfig);
        }


        $guzzleClientDefintion = new Definition('Majora\HttpBundle\Services\GuzzleWrapper');
        $guzzleClientDefintion->addArgument($config);

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

        $handlerStackDefinition = new Definition('\GuzzleHttp\Handler\CurlHandler');
        $handlerStackDefinition->setFactory(['GuzzleHttp\HandlerStack', 'create']);
        $container->setDefinition('majora_http.handler.'.$clientId, $handlerStackDefinition);

        $handlerStackReference = new Reference('majora_http.handler.'.$clientId);

        //Middleware
        $eventDispatcher = new Definition('Majora\HttpBundle\Middleware\MajoraEventDispatcher');
        $eventDispatcher->setArguments([new Reference('debug.stopwatch'), new Reference('event_dispatcher'), $clientId]);
        $eventDispatcher->addMethodCall('push', [$handlerStackReference]);


        $clientConfig['handler'] = $handlerStackReference;
        $clientConfig['middleware'] = $eventDispatcher;

        $guzzleClient= new Definition('Majora\HttpBundle\Services\GuzzleWrapper');
        $guzzleClient->addArgument($clientConfig);

        $container->setDefinition('guzzle_http.'.$clientId , $guzzleClient);
    }

}
