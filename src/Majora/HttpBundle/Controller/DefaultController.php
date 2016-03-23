<?php

namespace Majora\HttpBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Config\Definition\Exception\Exception;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $guzzle = $this->get('guzzle_http.velib');

        $response = $guzzle->request('GET', 'stations?contract=Paris&apiKey=d70eae09acd70154838abe62ddf56de93c40d55c');
     //   throw new Exception;
        return $this->render('MajoraHttpBundle:Default:index.html.twig');
    }

}