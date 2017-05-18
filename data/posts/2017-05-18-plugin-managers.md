---
layout: post
title: Leverage Zend Component Plugin Managers in Expressive
date: 2017-05-18T11:00:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-05-18-plugin-managers.html
categories:
- blog
- components
- expressive

---

With the release of Expressive 2, one of the key stories was the ability to
require ZF components within Expressive, and have their dependencies auto-wired
into your application courtesy of the [component installer](https://docs.zendframework.com/zend-component-installer/).

However, we recently had a user in our [Slack channel](https://zendframework.slack.com)
(need [an invite?](https://zendframework-slack.herokuapp.com)) indicating they
were having issues with usage of custom validators, filters, and input filters.
After a [more thorough writeup on our forums](https://discourse.zendframework.com/t/validatormanager-not-calling-custom-validator-factory/109),
I realized we'd missed something important when making these integrations, and
set out to solve it.

## The problem

In zend-mvc applications, zend-modulemanager aggregates `Module` classes
registered with the application, and, in the process, does things like merge all
configuration. One thing it also does is seed plugin managers. The process for
this is rather convoluted, as it uses a combination of configuration keys as
well as methods defined in `Module` classes in order to pull together all
configuration for a given plugin manager prior to initializing it.

What this means is that, in most cases, components can have a specific
configuration key they look under for service definitions specific to a given
plugin manager. As an example, for zend-filter, you might do something like:

```php
[
    'filters' => [
        'aliases' => [ /* ... */ ],
        'invokables' => [ /* ... */ ],
        'factories' => [ /* ... */ ],
        'delegators' => [ /* ... */ ],
    ],
]
```

All plugin manager configurations follow the same format, but use a different
top-level key.

The problem we ran into in Expressive is that while we have encouraged users to
do this, _the plugin managers were never seeded with this configuration_, as the
logic for that was relegated to zend-modulemanager integrations!

## The solution

Over the past few days, I identified seven affected components, and wrote
patches for each that fix the problem. What they do is check to see if a
zend-modulemanager-specific service is present, and, if not, they then check for
and pull the relevant configuration, and use it to configure the plugin manager.

The following releases now have these fixes:

- [zend-log 2.9.2](https://github.com/zendframework/zend-log/releases/release-2.9.2);
  enables the `log_processors`, `log_writers`, `log_filters`, and `log_formatters`
  keys for configuring the `ProcessorPluginManager`, `WriterPluginManager`,
  `FilterPluginManager`, and `FormatterPluginManager`, respectively.
- [zend-i18n 2.7.4](https://github.com/zendframework/zend-i18n/releases/release-2.7.4);
  enables the `translator_plugins` key for configuring the
  `Zend\I18n\Translator\LoaderPluginManager`.
- [zend-hydrator 2.2.2](https://github.com/zendframework/zend-hydrator/releases/2.2.2);
  enables the `hydrators` key for configuring the `HydratorPluginManager`.
- [zend-filter 2.7.2](https://github.com/zendframework/zend-filter/releases/release-2.7.2);
  enables the `filters` key for configuring the `FilterPluginManager`.
- [zend-validator 2.9.1](https://github.com/zendframework/zend-validator/releases/release-2.9.1);
  enables the `validators` key for configuring the `ValidatorPluginManager`.
- [zend-inputfilter 2.7.4](https://github.com/zendframework/zend-inputfilter/releases/release-2.7.4);
  enables the `input_filters` key for configuring the `InputFilterPluginManager`.
- [zend-form 2.10.2](https://github.com/zendframework/zend-form/releases/release-2.10.2);
  enables the `form_elements` key for configuring the `FormElementManager`.

The end result is that each of these components will now work with Expressive
out of the box just as they would in a zend-mvc application!

## Wrapup

I feel this is a huge step forward in usability of Expressive; in
fact, each of these components can now be more easily used in _any_ application,
which is a huge win. I'm hoping that with these releases, folks will start
evaluating them more seriously for inclusion in other middleware architectures
as well.

This problem would likely have not been identified as quickly without the new
community process and tools we now have in place. A Slack thread helped identify
a problem existed, and a forum post helped us get all the details necessary to
reproduce the issue, and then coordinate the patches.

If you have not yet signed up for Slack, you can [do so here](https://zendframework-slack.herokuapp.com).
If you have not yet visited our forums, [head over and start participating](https://discourse.zendframework.com)!

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
