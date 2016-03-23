<?php

namespace Majora\HttpBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;


class GuzzleWrapper extends Client
{
    protected $clientConfig;

    public $stat_array;


    /**
     * GuzzleWrapper constructor.
     * @param $client
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }


    public function setStatArray($stat_array)
    {
        $this->stat_array = $stat_array;

        return $this->stat_array;
    }

    public function getStatArray()
    {
        return $this->stat_array;
    }
}