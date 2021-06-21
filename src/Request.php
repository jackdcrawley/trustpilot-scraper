<?php

namespace JackCrawley\TrustpilotScraper;

use JackCrawley\TrustpilotScraper\Core\Scraper;

class Request
{
    /** @var array */
    private $path;

    /** @var array */
    private $parameters;

    /** @return void */
    public function __construct()
    {
        $this->path = explode('/', substr($_SERVER['PHP_SELF'], 1));
        $this->parameters = $_REQUEST;
    }

    /**
     * Handle the current request.
     * 
     * @return void
     */
    public function handle()
    {
        $data = (new Scraper())->search($this->parameters['query']);

        echo json_encode($data);
        exit;
    }
}