<?php

namespace MyCrawler;

/**
 * Interface for Crawler results storage. This must be passed as an argument to a crawler scan.
 */
interface CrawlerMetrics
{
    /**
     * Add a page to the results set.
     *
     * @param string  $url      URL of the page the crawler scraped.
     * @param mixed[] $data     Page scraper result data.
     * @param mixed[] $metadata Information about the crawl, namely time taken and status code.
     */
    public function addPage(string $url, array $data, array $metadata) : void;

    /**
     * Unfortunately, page scrapers and metrics are coupled. Metrics needs to be able to understand
     * the data being returned by the scraper and have a place to store it.
     *
     * To that ends, any implementation of CrawlerMetrics is responsible for creating a scraper which
     * will return suitable data for consumption.
     *
     * @param string $url URL of the page to be scraped.
     * @return PageScraper A page scraper that can process the page's structure for insight.
     */
    public function getScraper(string $url) : PageScraper;
}
