<?php

namespace MyCrawler;

/**
 * Slightly improved version of the Basic Page Scraper, which now understands and restricts the crawler to
 * resources internal to the domain. We do this to prevent our crawler from venturing out into the wild wild
 * web and turning into a bad movie starring Will Smith and Kevin Kline.
 */
class DomainPageScraper extends BasicPageScraper
{
    private $domain;

    /**
     * @param string $domain The domain we are crawling; any links to this domain will be considered internal.
     */
    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Scrape a web page for links, images, title, and plain-text content AS WELL AS identifying our links
     * by whether they are internal or external to our configured domain.
     *
     * @param string $html HTML contents of a web page to scrape.
     * @return mixed[] Free-form data scraped from the web page.
     */
    public function scrape(string $html) : array
    {
        $data = parent::scrape($html);

        // Separate page links into in-domain and out-domain.
        $domain = $this->domain;
        $data['internalLinks'] = array_filter($data['links'], function ($href) use ($domain) {
            $host = parse_url($href, PHP_URL_HOST);
            return !($host && strpos($host, $domain) === false);
        });
        $data['externalLinks'] = array_diff($data['links'], $data['internalLinks']);

        return $data;
    }

    /**
     * Returns the list of links that count as *internal* for the configured domain. Our crawler will not
     * accidentally hit Google or some other such site, staying nice and safe in its sandbox.
     *
     * @param mixed[] $data The results of a page scrape, which should have identified some links.
     * @return string[] List of URLs we can continue to process as part of our crawl.
     */
    public function followOn(array $data) : array
    {
        return $data['internalLinks'];
    }
}
