---
layout: post
title: Context-specific escaping with zend-escaper
date: 2017-05-16T11:30:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-05-16-zend-escaper.html
categories:
- blog
- components
- zend-escaper
- security

---

Security of your website is not just about mitigating and preventing things like
SQL injection; it's also about protecting your users as they browse the site
from things like cross-site scripting (XSS) attacks, cross-site request forgery
(CSRF), and more. In particular, you need to be very careful about how you
generate HTML, CSS, and JavaScript to ensure that you do not create such
vectors.

As the mantra goes, filter input, and _escape output_.

Believe it or not, escaping in PHP is not terribly easy to get right. For
example, to properly escape HTML, you need to use `htmlspecialchars()`, with the
flags `ENT_QUOTES | ENT_SUBSTITUTE`, and provide a character encoding. Who
really wants to write 

```php
htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8')
```

_every single time they need to escape a string for use in HTML_?

Escaping HTML attributes, CSS, and JavaScript each require a regular expression
to identify known problem strings, and a number of heuristics to replace unicode
characters with hex entities, each with different rules. While much of this can
be done with built-in PHP features, these features do not catch all potential
attack vectors. A comprehensive solution is required.

Zend Framework provides the [zend-escaper](https://docs.zendframework.com/zend-esscaper)
component to manage this complexity for you, exposing functionality for escaping
HTML, HTML attributes, JavaScript, CSS, and URLs to ensure they are safe for the
browser.

## Installation

zend-escaper only requires PHP (of at least version 5.5 at the time of writing),
and is installable via composer:

```php
$ composer require zendframework/zend-escaper
```

## Usage

While we considered making zend-escaper act as either functions or static
methods, there was one thing in the way: proper escaping requires knowledge of
the intended output character set. As such, `Zend\Escaper\Escaper` must first be
instantiated; once it has, you call methods on it.

```php
use Zend\Escaper\Escaper;

$escaper = new Escaper('iso-8859-1');
```

By default, if no character set is provided, it assumes `utf-8`; we recommend
using UTF-8 unless there is a compelling reason not to. As such, in most cases,
you can instantiate it with no arguments:

```php
use Zend\Escaper\Escaper;

$escaper = new Escaper();
```

The class provides five methods:

- `escapeHtml(string $html) : string` will escape the string so it may be safely
  used as HTML. In general, this means `<`, `>`, and `&` characters (as well as
  others) are escaped to prevent injection of unwanted tags and entities.
- `escapeHtmlAttr(string $value) : string` escapes a string so it may safely be
  used within an HTML attribute value.
- `escapeJs(string $js) : string` escapes a string so it may safely be used
  within a `<script>` tag. In particular, this ensures that the code injected
  cannot contain continuations and escape sequences that lead to XSS vectors.
- `escapeCss(string $css) : string` escapes a string to use as CSS within
  `<style>` tags; similar to JS, it prevents continuations and escape sequences
  that can lead to XSS vectors.
- `escapeUrl(string $urlPart) : string` escapes a string to use _within_ a URL;
  it should not be used to escape the entire URL itself. It _should_ be used to
  escape things such as the URL path, query string parameters, and fragment,
  however.

So, as examples:

```php
echo $escaper->escapeHtml('<script>alert("zf")</script>');
// results in "&lt;script&gt;alert(&quot;zf&quot;)&lt;/script&gt;"

echo $escaper->escapeHtmlAttr("<script>alert('zf')</script>");
// results in "&lt;script&gt;alert&#x28;&#x27;zf&#x27;&#x29;&lt;&#x2F;script&gt;"

echo $escaper->escapeJs("bar&quot;; alert(&quot;zf&quot;); var xss=&quot;true");
// results in "bar\x26quot\x3B\x3B\x20alert\x28\x26quot\x3Bzf\x26quot\x3B\x29\x3B\x20var\x20xss\x3D\x26quot\x3Btrue"

echo $escaper->escapeCss("background-image: url('/zf.png?</style><script>alert(\'zf\')</script>');");
// results in "background\2D image\3A \20 url\28 \27 \2F zf\2E png\3F \3C \2F style\3E \3C script\3E alert\28 \5C \27 zf\5C \27 \29 \3C \2F script\3E \27 \29 \3B"

echo $escaper->escapeUrl('/foo " onmouseover="alert(\'zf\')');
// results in "%2Ffoo%20%22%20onmouseover%3D%22alert%28%27zf%27%29"
```

As you can see from these examples, the component aggresively filters each
string to ensure it is escaped correctly for the context for which it is
intended.

How and where might you use this?

- Within templates, to ensure output is properly escaped.
  For example, [zend-view](https://docs.zendframework.com/zend-view) includes
  helpers for it; it would be easy to add such functionality to
  [Plates](http://platesphp.com) and other templating solutions.
- In email templates.
- In serializers for APIs, to ensure things like URLs or XML attribute data are
  properly escaped.
- In error handlers, to ensure error messages are escaped and do not contain XSS
  vectors.

The main point is that escaping _can_ be easy with zend-escaper; start securing
your output today!

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
