<?php
declare(strict_types=1);

use Http\Client\Curl\Client;
use Phalcon\Http\Message\RequestFactory;
use Phalcon\Http\Message\ResponseFactory;
use Phalcon\Http\Message\StreamFactory;

use MyCrawler\CrawlerAgent;
use MyCrawler\PageCrawler;

use MyCrawler\SampleCrawler\CrawlerMetrics;

class IndexController extends ControllerBase
{

    public function indexAction()
    {

    }

    // This is me being lazy. I don't want a new controller for one action.

    public function crawlAction()
    {
        $url = $this->request->getPost('url');
        if (!$url) {
            throw new Exception("A URL is required to initiate site crawl.");
        }

        $errors = [];
        try {
            $client = new Client(new ResponseFactory(), new StreamFactory());
            $requestFactory = new RequestFactory();

            $crawlerAgent = new CrawlerAgent($client, $requestFactory);
            $crawlerMetrics = new CrawlerMetrics($url);

            $crawler = new PageCrawler($crawlerAgent);
            $metrics = $crawler->scan($url, $crawlerMetrics);

            $pages = $metrics->getPages();
            $summary = $metrics->buildSummary();

            $this->view->url = $url;
            $this->view->pages = $pages;
            $this->view->summary = $summary;
        }
        catch (\Psr\Http\Client\NetworkExceptionInterface $ex) {
            $errors[] = "Sorry, that URL is not valid.";
            $this->view->errors = $errors;
        }
    }
}

