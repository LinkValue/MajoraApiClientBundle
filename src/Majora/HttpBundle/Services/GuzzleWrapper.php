<?php

namespace Majora\HttpBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;


class GuzzleWrapper extends Client
{
    protected $clientConfig;

    /**
     * GuzzleWrapper constructor.
     * @param $client
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }
}