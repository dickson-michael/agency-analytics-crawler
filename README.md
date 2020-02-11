# agency-analytics-crawler

This project is a coding test for Agency Analytics, and an excuse to try out the Phalcon MVC framework.

The goal is to create a decoupled crawler for a domain (in this case, agencyanalytics.com) and have it report:
- Number of pages crawled
- Number of a unique images
- Number of unique internal links
- Number of unique external links
- Avg page load
- Avg word count
- Avg Title length
- Table to display each page crawled and it's status code

## Components

The bulk of code is located at app/libraries/MyCrawler and exposes a multi-purpose crawler configuration.

There are two interfaces:
- **PageScraper**, which defines an api for Scraping HTML that our Crawler will delegate to for data extraction.
- **CrawlerMetrics**, which defines a storage backend and is (undesirably) coupled to a PageScraper for generating understandable data.

The two are intended to be structured loosely along the same lines as a tokenizer / parser with the former creating a feed for the latter to consume.

Implementations are:
- **BasicPageScraper**, a *PageScraper* which extracts data from a page without any knowledge outside the text being scraped itself.
- **DomainPageScraper**, a *PageScraper* which understands the concept of local vs. remote content, and separates the data from *BasicPageScraper* along those lines.
- **SampleCrawler\CrawlerMetrics**, a *CrawlerMetrics* which generates the required information for this project from a *DomainPageScraper*.

Crawler functionality itself is provided by:
- **PageCrawler**, URL fetch and traversal traversal logic, tracking visited and unvisited links.
- **CrawlerAgent**, something I debatably split out of *PageCrawler*, to handle the actual fetching and timing required. The principle is to replace this component to be able to generate more or better metadata.

Finally, there is one primitive helper library:
- **HtmlDocumentHelper**, which offers a simple wrapper around the DOMDocument and DOMXpath calls used for scraping. Not that XPath is hard to follow, but even a small amount of extra readability can be benefitial.

## Self-Analysis

As some degree of specialized subclassing ended up being required, it would probably be easier to have *PageCrawler* itself subclass into a *DomainPageCrawler* which ignored remote links. My goal was to consolidate all "local vs. remote" considerations into a single class, but this could just as easily be pushed into a helper function. Given *PageCrawler* must already perform URL resolution on returned links, it's trivial to have it simply check whether the requested URL and any resolved links have the same host as well. This better fits separation of concerns.

It's probably reasonable to use a factory to generate a new *PageScraper* for each page encountered. The application would then be capable of generating a PDF scraper or HTML scraper, or use separate scrapers for local vs. remote pages. That logic is not implemented, but informed some choices.

Arguably, the biggest problem in the design is that *CrawlerMetrics* acts as a factory for *PageScraper*. This is implemented to ensure that *CrawlerMetrics* is receiving data it can understand. In a design where everything is replacable using DI, this is very out of place. A potential change might be to have a single DI container that constructs *CrawlerMetrics* and *PageScraper* and is tasked solely with ensuring their interoperability.

Use of XPath for scraping is very helpful. It's hardly surprising I found this is also a recommended approach after settling on it. That said, I do have a bias towards XPath stemming from past work, and using another approach might have been more educational.

Finally, generating summary data from *CrawlerMetrics* data could be split off into another class. *CrawlerMetrics* would then be acting simply as a Data Mapper, saving and loading records from some persistent or temporary store (and likely be renamed accordingly). If this code were to actually see usage with a persistent datastore, this is a change that I'd want to make.
