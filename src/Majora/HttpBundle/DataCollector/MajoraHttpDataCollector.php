<?php

namespace  Majora\HttpBundle\DataCollector;


use Majora\HttpBundle\Event\MajoraHttpEvent;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class MajoraHttpDataCollector
 * @package Majora\HttpBundle\DataCollector
 */
class MajoraHttpDataCollector extends DataCollector
{


    public function __construct()
    {
        $this->data['majoraHttp'] = [
            'commands' => new \SplQueue(),
        ];
    }

    function collect(Request $request, Response $response, \Exception $exception = null)
    {

    }

    function getName()
    {
        return "majorahttp";
    }

    public function onRequest(MajoraHttpEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $body = (array) json_decode($response->getBody());

        $data = array(
            'responseBody' => $body,
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'headers' => $request->getHeaders(),
            'statusCode' => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'executionTime' => $event->getExecutionTime(),
        );

        $this->data['majoraHttp']['commands']->enqueue($data);
    }

    public function getCommands()
    {
        return $this->data['majoraHttp']['commands'];
    }

}