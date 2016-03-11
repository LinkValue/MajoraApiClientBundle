<?php

namespace  Majora\HttpBundle\DataCollector;


use Majora\HttpBundle\Event\Dispatcher\MajoraHttpEvent;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class MajoraHttpDataCollector
 * @package Majora\HttpBundle\DataCollector
 */
class MajoraHttpDataCollector extends DataCollector
{
    function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'message' => 'hello profiler',
        );
    }

    public function getMessage()
    {
        return $this->data['message'];
    }

    function getName()
    {
        return "majora.majora_http_collector";
    }

    public function onRequest(MajoraHttpEvent $event)
    {
        $request = $event->getRequest();
    }

}