<?php

namespace MyCrawler;

/**
 * Interface definition of a page scraper used by crawlers. Must be able to scrape a page and
 * return extracted data, as well as determine a list of links we can continue processing.
 */
interface PageScraper
{
    /**
     * Scrape a web page for usable data and return it to the caller.
     *
     * @param string $html HTML contents of a web page to scrape.
     * @return mixed[] Free-form data scraped from the web page.
     */
    public function scrape(string $html) : array;

    /**
     * From a web page's scraped data, determine a list of URLs we can continue on to process.
     *
     * @param mixed[] $data The results of a page scrape, which should have identified some links.
     * @return string[] List of URLs we can continue to process as part of our crawl.
     */
    public function followOn(array $data) : array;
}

