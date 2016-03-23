<?php

namespace Majora\HttpBundle\Middleware;


use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\PrepareBodyMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Majora\HttpBundle\Event\MajoraHttpEvent;
use Symfony\Component\Stopwatch\Stopwatch;

class MajoraEventDispatcher
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var array
     */
    protected $event;
    /**
     * @var string
     */
    protected $clientId;

    /**
     * MajoraEventDispatcher constructor.
     * @param $stopWatch
     * @param EventDispatcherInterface $eventDispatcher
     * @param $clientId
     */
    public function __construct(Stopwatch $stopWatch, EventDispatcherInterface $eventDispatcher, $clientId)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->clientId = $clientId;
        $this->stopWatch = $stopWatch;

       // var_dump($this->eventDispatcher, $this->clientId, $this->stopWatch);
    }

    /**
     * @param HandlerStack $stack
     * @return HandlerStack
     */
    public function push(HandlerStack $stack)
    {
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            $this->initEvent($request);
            return $request;
        }));


        $stack->push(function (callable $handler) {
            return function (
                RequestInterface $request,
                array $options
            ) use ($handler) {

                $promise = $handler($request, $options);

                return $promise->then(
                    function (ResponseInterface $response) use ($request) {

                        $this->dispatchEvent($response);
                        return $response;
                    },
                    function (\Exception $reason) use ($request) {
                        $this->dispatchEvent($request);
                        throw $reason;
                    }
                );
            };
        });
        return $stack;
    }


    /**
     * @param RequestInterface $request
     */
    protected function initEvent(RequestInterface $request)
    {
        $this->stopWatch->start('majoraEvent.'.$this->clientId);
        $event = new MajoraHttpEvent();
        $event->setRequest($request);
        $event->setExecutionStart();
        $event->setClientId($this->clientId);
        $this->event = $event;
    }

    /**
     * @param ResponseInterface $response
     */
    protected function dispatchEvent(ResponseInterface $response)
    {
        $this->event->setResponse($response);
        $this->event->setReason($response->getReasonPhrase());
        $this->stopWatch->stop('majoraEvent.'.$this->clientId);
        $this->event->setExecutionStop($this->stopWatch->getEvent('majoraEvent.'.$this->clientId)->getDuration());
        $this->eventDispatcher->dispatch(MajoraHttpEvent::EVENT_NAME, $this->event);
    }

}