---
layout: post
title: Develop Expressive Applications Rapidly Using CLI Tooling
date: 2017-04-11T16:45:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-04-11-expressive-tooling.html
categories:
- blog
- expressive

---

First impressions matter, particularly when you start using a new framework. As
such, we're striving to improve your first tasks with Expressive.

With the 2.0 release, we provided several migration tools, as well as tooling
for creating, registering, and deregistering middleware modules. Each was
shipped as a separate script, with little unification between them.

Today, we've pushed a unified script, `expressive`, which provides access to all
the migration tooling, module tooling, and new tooling to help you create
http-interop middleware. Our hope is to make your first few minutes with
Expressive a bit easier, so you can start writing powerful applications.

## Getting the tooling

If you haven't created an application yet:

```bash
$ composer create-project zendframework/zend-expressive-skeleton
```

will create a new project using the latest 2.0.2 release, which contains the new
`expressive` script.

If you are already using Expressive 2, you can get the latest tooling using the
following, regardless of whether or not you've previously installed it:

```bash
$ composer require --dev "zendframework/zend-expressive-tooling:^0.4.1"
```

## What tooling do you get?

The `expressive` script has three general categories of commands:

- **`migrate:*`**: these are intended for Expressive 1 users who are migrating to
  Expressive 2. We'll ignore these for now, as we've [covered them
  elsewhere](/blog/2017-03-13-expressive-2-migration.html).
- **`module:*`** Create, register, and deregister Expressive middleware modules.
- **`middleware:*`**: Create http-interop middleware class files.

## Create your first module

For purposes of illustration, we'll consider that you want to create an API for
listing books. You anticipate that the functionality can be self-contained, and
that you may want to potentially extract it later to re-use elsewhere. As such,
you have a good case for [creating a module](https://docs.zendframework.com/zend-expressive/features/modular-applications/).

Let's get started:

```bash
$ ./vendor/bin/expressive module:create BooksApi
```

The above does the following:

- It creates a directory tree for a `BooksApi` module under `src/BooksApi/`,
  with a subtree for source code, and another for templates.
- It creates the class `BooksApi\ConfigProvider` in the file
  `src/BooksApi/src/ConfigProvider.php`
- It adds a PSR-4 autoloader entry for `BooksApi` in your `composer.json`, and
  runs `composer dump-autoload` to ensure the new autoloader rule is generated
  within your application.
- It adds an entry for the generated `BooksApi\ConfigProvider` to your
  `config/config.php` file.

At this point, we have a module with no code! Let's rectify that situation!

## Create middleware

We know we will want to _list books_, so we'll create middleware for that:

```bash
$ ./vendor/bin/expressive middleware:create "BooksApi\Action\ListBooksAction"
```

> ### Use quotes!
>
> PHP's namespace separator is the backslash, which is typically interpreted as
> an escape character in most shells. As such, use double or single quotes
> around the middleware name to ensure it is passed correctly to the command!

This creates the class `BooksApi\Action\ListBooksAction` in the file
`src/BooksApi/src/Action/ListBooksAction.php`. In doing so, it creates the
`src/BooksApi/src/Action/` directory, as it did not previously exist!

The class file contents will look like this:

```php
namespace BooksApi\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\ServerRequestInterface;

class ListBooksAction implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // $response = $delegate->process($request);
    }
}
```

At this point, you're ready to start coding!

## Future direction

This tooling is just a start; we're well aware that developers will want and
need more tooling to make development more convenient. As such, we have a call
to action: please [open issues](https://github.com/zendframework/zend-expressive-tooling/issues/new)
to request more commands that will make your life easier, or open pull requests
that implement the tools you need. If you are unsure how to do so, use the
existing code to get an idea of how to proceed, or [ask in the
#expressive-contrib Slack channel](https://zendframework.slack.com/messages/C4QRP8ELU) (visit our [signup
page to get an invite](https://zendframework-slack.herokuapp.com)).

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
