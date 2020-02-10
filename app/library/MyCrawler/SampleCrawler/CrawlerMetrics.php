<?php

namespace MyCrawler\SampleCrawler;

use MyCrawler\DomainPageScraper;
use MyCrawler\PageScraper;

/**
 * For the sake of this sample application, this is the Metrics object responsible for holding
 * the results of the web site scraping.
 */
class CrawlerMetrics implements \MyCrawler\CrawlerMetrics
{
    /** @var mixed[] Dictionary of pages encountered and the data/metadata that was read. */
    private $pages;

    /** @var DomainPageScraper Singleton scraper configured for a single domain. Shared resource. */
    private $scraper = null;

    public function __construct()
    {
        $this->pages = [];
    }

    /**
     * Add a page to the results set.
     *
     * @param string  $url      URL of the page the crawler scraped.
     * @param mixed[] $data     Page scraper result data.
     * @param mixed[] $metadata Information about the crawl, namely time taken and status code.
     */
    public function addPage(string $url, array $data, array $metadata) : void
    {
        // TODO: We probably want to resolve the Links, InternalLinks, and ExternalLinks here. Helps with uniqueness.

        $this->pages[$url] = $data + $metadata;
    }

    /**
     * Returns the list of pages scraped in all their raw data. Passed directly to the view for this application.
     *
     * @return array List of pages scraped and the data/metadata that was read.
     */
    public function getPages() : array
    {
        return $this->pages;
    }

    /**
     * Builds a new scraper for the URL to be scraped. Scrapers are coupled to Metrics to ensure we are written to
     * consume and process the scraper output.
     *
     * @param string $url URL of the page to be scraped.
     * @return DomainPageScraper Page scraper configured for the input URL's domain.
     */
    private function makeScraper(string $url) : PageScraper
    {
        $topHost = parse_url($url, PHP_URL_HOST); // handles most formats
        $topDomain = implode('.', array_slice(explode('.', $topHost), -2)); // A.B.C.HOST.TLD -> HOST.TLD

        return new DomainPageScraper($topDomain);
    }

    /**
     * Returns a scraper for the URL. A single scraper is used for all queries, as this result metrics data is
     * built around separating internal links from foreign links.
     *
     * @param string $url URL of the page to be scraped.
     * @return DomainPageScraper Page scraper configured for the domain of the first URL (i.e. crawler entry point).
     */
    public function getScraper(string $url) : PageScraper
    {
        // We offer single-domain metrics, so we want a shared scraper for that domain.
        if (!isset($this->scraper)) {
            $this->scraper = $this->makeScraper($url);
        }
        return $this->scraper;
    }

    /**
     * Returns a Value Object comprising various summary informations about the pages queried.
     *
     * @return ValueObject A value object with a list of the pages crawled, images encountered, internal and
     *         external links, load times, word counts, and title lengths (per page).
     */
    public function buildSummary() : \stdClass
    {
        return (object) [
            'pagesCrawled' => array_keys(array_filter($this->pages, function($data) {
                return $data['code'] == 200;
            })),

            // Technically this is naive, as blank.gif and blank.png are the "same" image, but doing a vector analysis of the actual image data is a bit much.
            'uniqueImages' => array_unique(array_reduce($this->pages, function ($acc, $data) {
                return array_merge($acc, $data['images']);
            }, [])),

            'uniqueInternalLinks' => array_unique(array_reduce($this->pages, function ($acc, $data) {
                return array_merge($acc, $data['internalLinks']);
            }, [])),

            'uniqueExternalLinks' => array_unique(array_reduce($this->pages, function ($acc, $data) {
                return array_merge($acc, $data['externalLinks']);
            }, [])),

            'pagesLoadTime' => array_map(function ($data) {
                return $data['time'];
            }, $this->pages),

            'wordCount' => array_map(function ($data) {
                return str_word_count($data['text']);
            }, $this->pages),

            'titleLength' => array_map(function ($data) {
                return strlen($data['title']);
            }, $this->pages),
        ];
    }
}
