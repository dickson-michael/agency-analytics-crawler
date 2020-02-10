<?php

namespace MyCrawler;

/**
 * The page crawler logic. This crawler has an internal limiter to prevent running indefinitely. It will only
 * fetch a few pages at a time (pages randomly chosen).
 */
class PageCrawler
{
    /** @var CrawlerAgent Client configured to request a page and provide metadata. */
    private $agent;

    /** @var int The total number of pages we can request before terminating a scan. */
    private $crawlLimit;

    /**
     * @param CrawlerAgent $agent Dependency-injected http client and request factory.
     * @param int $crawlLimit     Total number of pages we can request before terminating a scan.
     */
    public function __construct(CrawlerAgent $agent, int $crawlLimit = 5)
    {
        $this->agent = $agent;
        $this->crawlLimit = $crawlLimit;
    }

    /**
     * Consume all the internets, starting at the input URL.
     *
     * @param string $url URL of a web address where the crawl will begin.
     * @param CrawlerMetrics $metrics Dependency-injected storage backend for the crawl's data.
     *        TODO: I think this should be a factory which generates both scraper and metrics. But it's harder to
     *              remain aware of the coupling between the two if done this way.
     *
     * @return CrawlerMetrics The resulting metrics of the site crawl.
     */
    public function scan(string $url, CrawlerMetrics $metrics) : CrawlerMetrics
    {
        $visited = [];
        $unvisited = [$url];

        for ($i = 0; $i < $this->crawlLimit && count($unvisited) > 0; $i++) {
            $rand = array_rand($unvisited);
            $url = $unvisited[$rand];

            $links = $this->crawlPage($url, $metrics);

            // Resolve crawled links to a form can query (e.g. 'xyz' -> 'site.tld/a/b/xyz' when at 'site.tld/a/b')
            $urlResolver = $this->makeResolver($url);
            $links = array_map(function ($url) use ($urlResolver) {
                return $urlResolver($url);
            }, $links);

            $visited[] = $url;
            $unvisited = array_merge($unvisited, $links);
            $unvisited = array_diff($unvisited, $visited);
        }

        return $metrics;
    }

    /**
     * Operative method for an individual URL. Will download, scrape, and find links at the given URL, storing
     * the result with the Metrics.
     *
     * @param string $url URL of a web address to process.
     * @param CrawlerMetrics $metrics Storage backend for the scraped data.
     *
     * @return string[] A list of encountered URLs which we should crawl as well.
     */
    private function crawlPage(string $url, CrawlerMetrics $metrics) : array
    {
        $scraper = $metrics->getScraper($url);

        // Fetch this URL and scrape it.
        $data = $this->agent->fetch($url);
        $data += $scraper->scrape($data['html']);
        unset($data['html']); // only returned for scraping

        $metrics->addPage($url, $data, $data);

        // And return the list of follow-on links.
        return $scraper->followOn($data);
    }

    /**
     * URL resolution is hard. This generates a curried version of resolve($base, $path) suitable for functional
     * usage (map/reduce et al).
     *
     * @param string $base Base URL we are feeding the resolver.
     *
     * @return callable A function that resolves any relative paths wrt to the base.
     */
    private function makeResolver($base) : callable
    {
        return function($path) use ($base) : string {
            // note: tested this with Phalcon\Uri instead, but it doesn't do resolution
            $uri = \GuzzleHttp\Psr7\UriResolver::resolve(
                \GuzzleHttp\Psr7\uri_for($base),
                \GuzzleHttp\Psr7\uri_for($path)
            );
            return (string) $uri;
        };
    }
}
