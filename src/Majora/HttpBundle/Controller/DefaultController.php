<?php

namespace Majora\HttpBundle\Controller;

use Majora\HttpBundle\Event\Dispatcher\MajoraHttpEvent;
use Majora\HttpBundle\Event\MajoraEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $guzzle = $this->get('guzzle_http.velib');
        $response = $guzzle->request('GET', 'stations?contract=Paris&apiKey=d70eae09acd70154838abe62ddf56de93c40d55c');


        $event = new MajoraHttpEvent();
        $dispatcher =  $this->get('event_dispatcher');
        $dispatcher->dispatch(MajoraEvents::onGuzzleRequest, $event);


        return $this->render('MajoraHttpBundle:Default:index.html.twig');
    }
}
