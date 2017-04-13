---
layout: post
title: Create RSS and Atom Feeds
date: 2017-04-13T08:30:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-04-13-zend-feed-writing.html
categories:
- blog
- components
- zend-feed

---

In our previous article on zend-feed, we [detailed RSS and Atom feed discovery
and parsing](/blog/2017-04-06-zend-feed-reading.html). Today, we're going to
cover its complement: feed creation!

zend-feed provides the ability to create both Atom 1.0 and RSS 2.0 feeds, and
even supports custom extensions during feed generation, including:

- Atom (`xmlns:atom`; RSS 2 only): provide links to Atom feeds and Pubsubhubbub
  URIs within your RSS feed.
- Content (`xmlns:content`; RSS 2 only): provide CDATA encoded content for
  individual feed items.
- DublinCore (`xmlns:dc`; RSS 2 only): provide metadata around common content
  elements such as author/publisher/contributor/creator, dates, languages, etc.
- iTunes (`xmlns:itunes`): create podcast feeds and items compatible with
  iTunes.
- Slash (`xmlns:slash`; RSS 2 only): communicate comment counts per item.
- Threading (`xmlns:thr`; RSS 2 only): provide metadata around _threading_
  feed items, including indicating what an item is _in reply to_, linking to
  _replies_, and metrics around each.
- WellFormedWeb (`xmlns:wfw`; RSS 2 only): provide a link to a separate comments
  feed for a given entry.

You can also provide your own custom extensions if desired; these are just what
we ship out of the box! In many cases, you don't even need to know about the
extensions, as zend-feed will take care of adding in those that are required,
based on the data you provide in the feed and entries.

## Creating a feed

The first step, of course, is having some content! I'll assume you have items
you want to publish, and those will be in `$data`, which we'll loop over. How
that data looks will be dependent on your application, so please be aware that
you may need to adjust any examples below to fit your own data source.

Next, we need to have zend-feed installed; do that via Composer:

```bash
$ composer require zendframework/zend-feed
```

Now we can finally get started. We'll begin by creating a feed, and populating
it with some basic metadata:

```php
use Zend\Feed\Writer\Feed;

$feed = new Feed();
// Title of the feed
$feed->setTitle('Tutorial Feed');
// Link to the feed's target, usually a homepage:
$feed->setLink('https://example.com/');
// Link to the feed itself, and the feed type:
$feed->setFeedLink('https://example.com/feed.xml', 'rss');

// Feed description; only required for RSS:
$feed->setDescription('This is a tutorial feed for example.com');
```

A couple things to note: First, you need to know what _type_ of feed you're
creating up front, as it will affect what properties _must_ be set, as well as
which are actually available. I personally like to generate feeds of both types,
so I'll do the above within a method call that accepts the feed type as an
argument, and then puts some declarations within conditionals based on that
type.

Second, you'll need to know the fully-qualified URIs to the feed target and the
feed itself. These will generally be something you generate; most routing
libraries will have these capabilities, and you'll generate these within your
application, instead of hard-coding them as I have done here.

## Adding items

Now that we have our feed, we'll loop over our data set and add items. Items
generally have:

- a title
- a link to the item
- an author
- the dates when it was modified, and last updated
- content

Putting it together:

```php
$latest = new DateTime('@0');
foreach ($data as $datum) {
    // Create an empty entry:
    $entry = $feed->createEntry();

    // Set the entry title:
    $entry->setTitle($datum->getTitle());

    // Set the link to the entry:
    $entry->setLink(sprintf('%s%s.html', $baseUri, $datum->getId()));

    // Add an author, if you can. Each author entry should be an
    // array containing minimally a "name" key, and zero or more of
    // the keys "email" or "uri".
    $entry->addAuthor($datum->getAuthor());

    // Set the date created:
    $entry->setDateCreated(new DateTime($datum->getDateCreated()));

    // And the date last updated:
    $modified = new DateTime($datum->getDateModified());
    $entry->setDateModified($modified);

    // And finally, some content:
    $entry->setContent($datum->getContent());

    // Add the new entry to the feed:
    $feed->addEntry($entry);

    // And memoize the date modified, if it's more recent:
    $latest = $modified > $latest ? $modified : $latest;
}
```

There are quite a few other properties you can set, and some of these will vary
based on custom extensions you might register with the feed; the above are the
typical items you'll include in a feed entry, however.

What is that bit about `$latest`, though?

Feeds need to have a timestamp indicating when they were most recently modified.

Why? Because feeds are intended to be read by _machines_ and aggregators, and
need to know when _new_ content is available.

You could set the date of modification to whatever the current timestamp is at
time of execution, but it's better to have it in sync with the most recent entry
in the feed itself. As such, the above code creates a timestamp set to timestamp
0, and checks for a modified date that is newer on each iteration.

Once we have that in place, we can add the modified date to the feed itself:

```php
$feed->setDateModified($latest);
```

## Rendering the feed

Rendering the feed involves _exporting_ it, which requires knowing the feed
_type_; this is necessary so that the correct XML markup is generated.

So, let's create an RSS feed:

```php
$rss = $feed->export('rss');
```

If we wanted, and we have the correct properties present, we can also render
Atom:

```php
$atom = $feed->export('atom');
```

Now what?

I often pre-generate feeds and cache them to the filesystem. In that case, a
`file_put_contents()`  call, using the generated feed as the string contents, is
all that's needed.

If you're serving the feed back over HTTP, you will want to send back the
correct HTTP `Content-Type` when you do. Additionally, it's good to send back a
`Last-Modified` header with the same date as the feed's own last modified date,
and/or an ETag with a hash of the feed; these allow clients performing HEAD
requests to determine whether or not they need to retrieve the full content, or
if they already have the latest.

If you are using PSR-7 middleware, these processes might look like this:

```php
use Zend\Diactoros\Response\TextResponse;

$commonHeaders = [
    'Last-Modified' => $feed->getDateModified()->format('c'),
    'ETag' => hash('sha256', $feed)
];

// For an RSS feed:
return new TextResponse($rss, 200, array_merge(
    $commonHeaders,
    ['Content-Type' => 'application/rss+xml']
));

// For an Atom feed:
return new TextResponse($atom, 200, array_merge(
    $commonHeaders,
    ['Content-Type' => 'application/atom+xml']
));
```

## Summing up

zend-feed's generation capabilities are incredibly flexible, while making the
general use-case straight-forward. We have created feeds for blog posts,
releases, tweets, and commenting systems using the component; it does exactly
what it advertises.

Visit the [zend-feed documentation](https://docs.zendframework.com/zend-feed/)
for more information.

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
