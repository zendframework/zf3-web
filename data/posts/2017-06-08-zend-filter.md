---
layout: post
title: Filter input using zend-filter
date: 2017-06-08T16:30:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-06-08-zend-filter.html
categories:
- blog
- components
- zend-filter
- security

---

When securing your website, the mantra is "Filter input, escape output." We
previously covered escaping output with our [post on
zend-escaper](/blog/2017-05-16-zend-escaper.html). We're now going to turn to
_filtering input_.

Filtering input is rather complex, and spans a number of practices:

- Filtering/normalizing input. As an example, your web page may have a form that
  allows submitting a credit card number. These have a variety of formats that
  may include spaces or dashes or dots &mdash; but the only characters that are
  of importance are the _digits_. As such, you will want to _normalize_ such
  input to strip out the unwanted characters.
- Validating input. Once you have done such normalization, you can then check
  to see that the data is actually _valid_ for its context. This may include one
  or more rules. Using our credit card example, you might first check it is of
  an appropriate length, and then verify that it begins with a known vendor
  digit, and only after those pass, validate the number against a online
  service.

For now, we're going to look at the first item, filtering and normalizing input,
using the component [zend-filter](https://docs.zendframework.com/zend-filter/).

## Installation

To install zend-filter, use Composer:

```bash
$ composer require zendframework/zend-filter
```

Currently, the only _required_ dependency is zend-stdlib. However, a few other
components are suggested, based on which filters and/or featurse you may want to
use:

- zendframework/zend-servicemanager is used by the `FilterChain` component for
  looking up filters by their _short name_ (versus fully qualified class name).
- zendframework/zend-crypt is used by the encryption and decryption filters.
- zendframework/zend-uri is used by the `UriNormalize` filter.
- zendframework/zend-i18n is used by several filters that provide
  internationalization features.

For our examples, we'll be using the `FilterChain` functionality, so we will
also want to install zend-servicemanager:

```bash
$ composer require zendframework/zend-servicemanager
```

## FilterInterface

Filters can be one of two things: a callable that accepts a single argument (the
value to filter), or an instance of `Zend\Filter\FilterInterface`:

```php
namespace Zend\Filter;

interface FilterInterface
{
    public function filter($value);
}
```

The value can be literally anything, and the filter can return anything itself.
Generally speaking, if a filter cannot operate on the value, it is expected to
return it verbatim.

zend-filter provides a few dozen filters for common operations, including things
like:

- Normalizing strings, integers, etc. to their corresponding boolean values.
- Normalizing strings representing integers to integer values.
- Normalizing empty values to null values.
- Normalizing input sets representing date and/or time selections from forms to
  `DateTime` instances.
- Normalizing URI values.
- Comparing values to whitelists and blacklists.
- Trimming whitespace, stripping newlines, and removing HTML tags or entities.
- Upper and lower casing words.
- Stripping everything but digits.
- Performing PCRE regexp replacements.
- Word inflection (camel-case to underscores and vice versa, etc.).
- Decrypting and encrypting file contents, as well as casting file contents to
  lower or upper case.
- Compressing and decompressing values.
- Decrypting and encrypting values.

Any of these may be used by themselves. However, in most cases, if that's all
you're doing, you might as well just do the functionality inline. So, what's the
benefit of zend-filter?

Chaining filters!

## FilterChain

When we get input from the web, it generally comes as strings, and is the result
of user input. As such, we often get a lot of garbage: extra spaces, unnecessary
newlines, HTML characters, etc.

When filtering such input, we might want to perform several operations:

```php
$value = $request->getParsedBody()['phone'] ?? '';
$value = trim($value);
$value = preg_replace("/[^\n\r]/", '', $value);
$value = preg_replace('/[^\d]/', '', $value);
```

We then need to test our code to ensure that we're filtering correctly.
Additionally, if at any point we fail to re-assign, we may lose the changes we
were performing!

With zend-filter, we can instead use a `FilterChain`. The above example becomes:

```php
use Zend\Filter\FilterChain;

$filter = new FilterChain();
// attachByName uses the class name, minus the namespace, and 
$filter->attachByName('StringTrim');
$filter->attachByName('StripNewlines');
$filter->attachByName('Digits');
$value = $filter->filter($request->getParsedBody()['phone'] ?? '');
```

Here's another example: let's say we have configuration keys that are in
`snake_case_format`, and which may be read from a file, and we wish to convert
those values to `CamelCase`.

```php
use Zend\Filter;

$filter = new Filter\FilterChain();
// attach lets you provide the instance you wish to use; this will work
// even without zend-servicemanager installed.
$filter->attach(new Filter\StringTrim());
$filter->attach(new Filter\StripNewlines()); // because we may have \r characters
$filter->attach(new Filter\Word\UnderscoreToCamelCase());

$configKeys = array_map([$filter, 'filter'], explode("\n", $fileContents));
```

This new example demonstrates a key feature of a `FilterChain`: you can re-use
it! Instead of having to put the code for normalizing the values within an
`array_map` callback, we can instead directly use our already configured
`FilterChain`, invoking it once for each value!

## Wrapping up

zend-filter can be a powerful tool in your arsenal for dealing with user input.
Paired with good validation, you can protect your application from malicious or
malformed input.

In the next post, we'll discuss zend-validation!

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
