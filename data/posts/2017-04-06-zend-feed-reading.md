---
layout: post
title: Discover and Read RSS and Atom Feeds
date: 2017-04-06T14:00:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-04-06-zend-feed-reading.html
categories:
- blog
- components
- zend-feed

---

Remember RSS and Atom feeds?

Chances are, you may be reading this _because_ it was on a feed:

- A number of Twitter services poll feeds and send links when new entries are
  discovered.
- Some of you may be using feed readers such as [Feedly](https://feedly.com)
- Many news aggregator services, including tools such as Google Now, use RSS and
  Atom feeds as sources.

An interesting fact: Atom itself is often used as a data transfer format for
REST services, particularly content management platforms! As such, being
familiar with feeds and having tools to work with them is an important skill for
a web developer!

In this first of a two part series on feeds, we'll look at feed _discovery_, as
well as _reading_, using zend-feed's Reader subcomponent.

## Getting started

First, of course, you need to install zend-feed:

```bash
$ composer require zendframework/zend-feed
```

As of version 2.6.0, the component has a very minimal set of dependencies: it
only requires zendframework/zend-escaper and zendframework/zend-stdlib in order
to work. It has a number of additional, optional requirements depending on
features you want to opt-in to:

- psr/http-message and/or zend-http, to allow polling pages for feeds, feeds
  themselves, or PubSubHubbub services.
- zendframework/zend-cache, to allow caching feeds between requests.
- zendframework/zend-db, which is used when using the PubSubHubbub subcomponent,
  in order for PuSH subscribers to store updates.
- zendframework/zend-validator, for validating addresses used in Atom feeds and
  entries when using the Writer subcomponent.

For our examples, we will need an HTTP client in order to fetch pages. For the
sake of simplicity, we'll go ahead and use zendframework/zend-http; if you are
already using Guzzle in your application, you can create a wrapper for it
[following instructions in the zend-feed manual](https://docs.zendframework.com/zend-feed/psr7-clients/).

```bash
$ composer require zendframework/zend-http
```

Now that we have these pieces in place, we can move on to link discovery!

## Link discovery

The Reader subcomponent contains facilities for finding Atom and RSS links
within an HTML page. Let's try this now:

```php
// In discovery.php:

use Zend\Feed\Reader\Reader;

require 'vendor/autoload.php';

$feedUrls  = [];
$feedLinks = Reader::findFeedLinks('https://framework.zend.com');

foreach ($feedLinks as $link) {
    switch ($link['type']) {
        case 'application/atom+xml':
            $feedUrls[] = $link['href'];
            break;
        case 'application/rss+xml':
            $feedUrls[] = $link['href'];
            break;
    }
}

var_export($feedUrls);
```

If you run the above, you should get a list like the following (at the time of writing):

```text
array (
  0 => 'https://framework.zend.com/security/feed',
  1 => 'https://framework.zend.com/blog/feed-atom.xml',
  2 => 'https://framework.zend.com/blog/feed-rss.xml',
  3 => 'https://framework.zend.com/releases/atom.xml',
  4 => 'https://framework.zend.com/releases/rss.xml',
)
```

That's rather useful! We can poll a page to discover links, and then follow
them!

Internally, the returned `$feedLinks` is a `Zend\Feed\Reader\FeedSet` instance,
which is really just an `ArrayObject` where each item it composes is itself a
`FeedSet` with specific attributes set (including the `type`, `href`, and `rel`,
usually). It only returns links that are known feed types; any other type of
link is ignored.

## Reading a feed

Now that we know where some feeds are, we can read them.

To do that, we pass a URL for a feed to the reader, and then pull data from the
returned feed:

```php
// In reader.php:

use Zend\Feed\Reader\Reader;

require 'vendor/autoload.php';

$feed = Reader::import('https://framework.zend.com/releases/rss.xml');

printf(
    "[%s](%s): %s\n",
    $feed->getTitle(),
    $feed->getLink(),
    $feed->getDescription()
);
```

The above will result in:

```text
[Zend Framework Releases](https://github.com/zendframework): Zend Framework and zfcampus releases
```

The above is considered the feed _channel data_; it's information about the feed
itself. Most likely, though, we want to know what _entries_ are in the feed!

## Getting feed entries

The feed returned by `Reader::import()` is itself _iterable_, which each item of
iteration being an _entry_. At its most basic:

```php
foreach ($feed as $entry) {
    printf(
        "[%s](%s): %s\n",
        $entry->getTitle(),
        $entry->getLink(),
        $entry->getDescription()
    );
}
```

This will loop through each entry, listing the title, the canonical link to the
item, and a description of the entry.

The above will work across any type of feed. However, feed capabilities vary
based on type. RSS and Atom feed entries will have different data available; in
fact, Atom is considered an _extensible_ protocol, which means that such entries
can potentially expose quite a lot of additional data!

You may want to read up on what's available:

- [RSS entry properties](https://docs.zendframework.com/zend-feed/consuming-rss/#get-properties)
- [Atom entries](https://docs.zendframework.com/zend-feed/consuming-atom/)

## Until next time

zend-feed's Reader subcomponent offers a number of other capabilities,
including:

- Importing actual feed _strings_ (versus fetching via an HTTP client)
- The ability to utilize alternate HTTP clients.
- The ability to extend the Atom protocol in order to access additional data.

The zend-feed component has [extensive documentation](https://docs.zendframework.com/zend-feed/),
which will answer most questions you may have at this point.

We hope this quick primer gets you started consuming feeds; in our next post,
we'll demonstrate how you can create feeds!
