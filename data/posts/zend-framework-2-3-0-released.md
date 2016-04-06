---
layout: post
title: Zend Framework 2.3.0 Released!
date: 2014-03-12 19:00
update: 2014-03-12 19:00
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/zend-framework-2-3-0-released.html
categories:
- blog
- released

---

 The Zend Framework community is pleased to announce the immediate availability of Zend Framework 2.3.0!

- [http://framework.zend.com/downloads/latest](/downloads/latest)

 This is our first minor release in 10 months, providing the first new features since May of 2013.

 Among those features, we've updated our minimum supported PHP version to 5.3.23, fixed a large number of issues with how form collections work, improved performance of the service manager, and much, much more.

New minimum supported PHP version
---------------------------------

This release ups the minimum required PHP version from 5.3.3 to **5.3.23**. Making this change affords the following:

- 5.3.9 and up have a fix that allows a class to implement multiple interfaces that define the same method, so long as the signatures are compatible. Prior to that version, doing so raised a fatal error. This change is necessary in order to solve a problem with separated interface usage in the framework.
- 5.3.23 contains a fix for [PHP bug #62672](https://bugs.php.net/bug.php?id=52861). Adopting this version or greater will allow us to (eventually) remove polyfill support that works around the symptoms of that issue.

New Additions / Improvements
----------------------------

 More than 230 pull requests and issues were closed for this release -- far too many to list individually. That said, there are quite a few incremental improvements that will be of interest to Zend Framework 2 users. Below is a list broken down by component.

### Zend\\Authentication

- [\#4815](https://github.com/zendframework/zf2/issues/4815) promotes `Zend\AuthenticationService\Adapter\Http`'s `_challengeClient()` method to public visibility, and renames it to `challengeClient()`; the old method remains as a proxy to the new one. This allows implementors to issue the HTTP credential challenge manually.
- [\#5901](https://github.com/zendframework/zf2/issues/5901) adds an `AuthenticationServiceInterface`, to allow alternate implementations.

### Zend\\Cache

- [\#4512](https://github.com/zendframework/zf2/issues/4512) introduces a `BlackHole` cache storage adapter; this adapter is useful during development, when you do not want cache operations to have effect, but need to test that a system using caching works.
- `Zend\Cache\Storage\Adapter\Apc` now supports "check and set" operations, per [\#4844](https://github.com/zendframework/zf2/issues/4844).
- [\#5829](https://github.com/zendframework/zf2/issues/5829) adds a new cache adapter, `Memcache` (not to be confused with `Memcached`), for use with `ext/memcache`.

### Zend\\Code

- [\#4989](https://github.com/zendframework/zf2/issues/4989) adds the ability to identify PHP traits in the `TokenArrayScanner`.
- [\#6262](https://github.com/zendframework/zf2/issues/6262) adds a `getPrototype()` method to `MethodReflection`; this returns a structured array detailing the namespace, class, visibility, and arguments (including names, default values, and types) for the method.
- [\#5400](https://github.com/zendframework/zf2/issues/5400) adds the capability for the `PropertyScanner` to determine the PHP type of a given object property, via the new method `getValueType()`.

### Zend\\Config

- [\#4824](https://github.com/zendframework/zf2/issues/4824) adds a `JavaProperties` configuration reader.
- [\#4860](https://github.com/zendframework/zf2/issues/4860) provides an abstract factory for retrieving named top-level configuration keys from the `Config` service. As an example, if you have a key `zf-apigility`, you can now retrieve it from the service manager using `config-zf-apigility` or `zf-apigility-config`. Namespaces are also often-used for top-level keys, and notations such as `ZF\Apigility\Config` may be used, too.
- A number of improvements were made to the `PhpArray` config writer to make the output it generates more readable, as well as more consistent with the values being passed as input. These include consistent 4-space indentation; putting the opening `array` declarations on the same line as `=>` operators; ensuring boolean values are written as booleans; ensuring strings are written with proper, and readable, escapeing; allowing writing arrays using PHP 5.4 short-array syntax; and making attempts to replace paths using `__DIR__` notation when possible.

### Zend\\Console

- [\#4449](https://github.com/zendframework/zf2/issues/4449) moves the console routing logic out of `Zend\Mvc` and into `Zend\Console\ConsoleRouteMatcher`. This allows re-use of the `Zend\Console` component in a standalone fashion. `Zend\Mvc\Router\Console\Simple` was refactored to consume a `ConsoleRouteMatcher` instance internally.
- [\#4606](https://github.com/zendframework/zf2/issues/4606) adds support for `Zend\Console` to detect the console encoding, and use that when emitting text.
- [\#5711](https://github.com/zendframework/zf2/issues/5711) implements the `writeTextBlock()` method in the `AbstractAdapter`, allowing the ability to specify a block size and text to wrap within that block when generating console output.
- [\#5720](https://github.com/zendframework/zf2/issues/5720) fixes console routing to ensure CamelCase values in routes will be treated as literals, and ALLCAPS can be used to define value parameters.
- [\#5713](https://github.com/zendframework/zf2/issues/5713) adds the ability to specify option callback hooks in `Zend\Console\Getopt`. As examples:
 

    $opts->setOptionCallback('apple' function ($value, $opts) {
          echo "You want a $value apple!\n";
    });


Essentially, once `parse()` is called, if the specified option was provided, the callback will be triggered. Returning a boolean `false` will cause cause `parse()` to invalidate usage, raising an exception.

### Zend\\Crypt

- [\#5024](https://github.com/zendframework/zf2/issues/5024) removes the `KEY_DERIV_HMAC` constant, and allows the ability to specify alternate PBKDF2 hashing algorithms within the `Zend\Crypt\BlockCipher` class.

### Zend\\Db

- `Zend\Db\Sql` with MySQL can utilize a `Select` object containing an `OFFSET` without `LIMIT`
- `Zend\Db\Sql`'s `In` predicate now supports subselects
- `Zend\Db\Sql` now has a `NotIn` predicate.
- A method `inTransaction()` has been added to all `Zend\Db\Adapter` drivers
- `Zend\Db\Sql\Select`'s `from()` can be a subselect
- `Zend\Db\Sql\Insert` can use a Select object as the value source `(INSERT INTO ... SELECT)`
- `Zend\Db\Adapter` PDO now accepts a charset when creating the DSN

### Zend\\Dom

- [\#5356](https://github.com/zendframework/zf2/issues/5356) provides a backwards-compatible rewrite of the `Zend\Dom\Query` component and logic. It presents a new class, `Zend\Dom\Document`, along with a subcomponent of the same name containing new `Query` and `Nodelist` classes. Usage becomes:
 

    use ZendDomDocument;
    $document = new Document($htmlXmlOrFile, $docType, $encoding);
    foreach (DocumentQuery($expression, $document, $xpathOrCssQueryType) as $match) {
        // do something with matching DOMNode
    }
    // More concretely:
    $document = new Document($someHtml, 'DOC_HTML', 'utf-8');
    foreach (DocumentQuery('img.current', $document, 'TYPE_CSS') as $match) {
        $source = $document->attributes->getNamedItem('src');
    }


`Zend\Dom\Query` and `Zend\Dom\Css2Xpath` have been deprecated in favor of the new API. `Zend\Test\PHPUnit` still needs to be updated to use the new API, however.

### Zend\\EventManager

- [\#5283](https://github.com/zendframework/zf2/issues/5283) deprecates the `ProvidesEvents` trait in favor of the `EventManagerAwareTrait`; the latter is named after the interface it implements, and includes the now standard `Trait` suffix.

### Zend\\Filter

- [\#5436](https://github.com/zendframework/zf2/issues/5436) refactors `Zend\Filter` to ensure consistency throughout the component. Filters now never trigger errors or throw exceptions; if a filter cannot handle an incoming input, it will return it unmodified.

### Zend\\Form

- [\#4400](https://github.com/zendframework/zf2/issues/4400) allows you to pass the string name of the element you want to create as the second argument when using `Zend\Form\FormElementManager::get()` - instead of requiring that you pass it in as `array('name' => 'name value')`.
- The `Zend\Form` component has had a number of improvements surrounding HTML escaping and form labels. Among these is the addition of `LabelAwareInterface`, which defines methods for an element or fieldset to provide a label, label attributes, and label options (one of which is the option `disable_html_escape`, allowing developers to provide markup within the label text). Many efforts have been made to keep this functionality backwards compatible, while simultaneously ensuring that proper defaults are provided.
- Numerous improvements were made to how form Collections are managed, including improvements to counts, managing input filters, handling nested sets, binding objects, and more.
- [\#5918](https://github.com/zendframework/zf2/issues/5918) ensures that multiple CSRF elements on the same page with the same name should not conflict, and still validate.
- [\#4846](https://github.com/zendframework/zf2/issues/4846) adds the ability to disable the `InArray` validator when defining a `MultiCheckbox` form element.
- [\#4884](https://github.com/zendframework/zf2/issues/4884) provides the ability to replace elements within a form collection.
- [\#4927](https://github.com/zendframework/zf2/issues/4927) adds the ability to provide a `Traversable` value to a nested fieldset in a form.
- [\#4971](https://github.com/zendframework/zf2/issues/4971) updates the form factory to allow specifying `null` configuration values. This allows one module to override and cancel the setting of another when desired.
- [\#5420](https://github.com/zendframework/zf2/issues/5420) adds the ability to compose `Zend\Form` collections via annotations.
- [\#5456](https://github.com/zendframework/zf2/issues/5456) adds the ability for annotations to provide input filter specifications when provided on an object representing a fieldset.
- [\#5562](https://github.com/zendframework/zf2/issues/5562) adds the `unsetValueOption()` method to `Select` and `MultiCheckbox` element types.

### Zend\\Http

- [\#4950](https://github.com/zendframework/zf2/issues/4950) adds `match()` capabilities to the `ContentType` header class, similar to the implementation for `Accept` header instances. This allows matching incoming data against a mimetype in order to perform content negotiation.
- [\#5029](https://github.com/zendframework/zf2/issues/5029) adds a new header class for `Origin` headers.
- [\#5316](https://github.com/zendframework/zf2/issues/5316) adds a new header class for `Content-Security-Policy` headers.
- [\#5732](https://github.com/zendframework/zf2/issues/5732) adds the ability to set custom HTTP response status codes via a new `Response` method, `setCustomStatusCode()`.

### Zend\\I18n

- [\#4510](https://github.com/zendframework/zf2/issues/4510) introduces `Zend\I18n\Filter\NumberParse`, which will filter a string parseable by PHP's built-in `NumberFormatter` to a number.
- [\#5034](https://github.com/zendframework/zf2/issues/5034) makes the `PhoneNumber` validator `Locale`-aware.
- [\#5108](https://github.com/zendframework/zf2/issues/5108) introduces a `TranslatorInterface`, defining the methods `translate()` and `translatePlural()`. This will allow for alternate implementations, but also for other components to create equivalent, component-specific interfaces, and thus reduce dependencies.
- [\#5825](https://github.com/zendframework/zf2/issues/5825) adds a new translation loader, `PhpMemoryArray`. It behaves like the `PhpArray` loader, but instead of accepting a file that returns an array, it accepts an array of translations directly. This allows specifying translations as part of configuration, or via a caching system.

### Zend\\InputFilter

- A number of updates were made regarding how collection input filters work to ensure they are more consistent, and operate according to user expectations with regard to empty sets, nested sets, etc.

### Zend\\Json

- [\#5933](https://github.com/zendframework/zf2/issues/5933) provides the ability to use arbitrary response codes with `Zend\Json\Server`.

### Zend\\Loader

- [\#5783](https://github.com/zendframework/zf2/issues/5783) fixes the `StandardAutoloader` such that if a namespace matches, but no matching class is found, it will continue to loop through any other namespaces present. This fixes a situation whereby a map for a subnamespace may be registered later than the parent; prior to the change, the subnamespace would never be matched.

### Zend\\Log

- [\#4455](https://github.com/zendframework/zf2/issues/4455) adds new service providers for `Zend\Log`: `log_writers` and `log_processors`. These allow you to provide custom log writer and processor services for use with the `Zend\Log\LoggerAbstractServiceFactory`.
- [\#4742](https://github.com/zendframework/zf2/issues/4742) provides a new interface, `Zend\Log\LoggerAwareInterface`, for hinting that an object composes, or can compose, a `Zend\Log\Logger` instance. A corresponding PHP Trait is also provided.
- [\#5875](https://github.com/zendframework/zf2/issues/5875) adds a `registerFatalErrorShutdownFunction()` method to `Zend\Log\Logger`, to handle logging fatal runtime errors.

### Zend\\Mail

- [\#5261](https://github.com/zendframework/zf2/issues/5261) adds a new `NullTransport` to `Zend\Mail`, providing a no-op mail transport. This can be useful in non-production environments, or when needing to selectively disable mail sending capabilities without altering code.
- [\#5470](https://github.com/zendframework/zf2/issues/5470) adds `Zend\Mail\Transport\Factory`, for simplifying creation of a mail transport via configuration.

### Zend\\Mvc

- [\#4849](https://github.com/zendframework/zf2/issues/4849) updates `Zend\Mvc\Application::run()` such that it now always returns the `Application` instance. If an event returns a response object, it is always pushed into the `Application` instance now so that it may be retrieved after `run()` has finished executing.
- [\#4962](https://github.com/zendframework/zf2/issues/4962) modifies the various MVC factories to reference the service `ControllerManager` instead of `ControllerLoader` (which is a legacy name from early beta releases); `ControllerManager` was made an alias of `ControllerLoader`. This change future-proofs the MVC. If you are using `ControllerLoader` in your own code, we encourage you to change those references to `ControllerManager` (though `ControllerLoader` will continue to work for the foreseeable future).
- [\#5108](https://github.com/zendframework/zf2/issues/5108) introduces a `DummyTranslator`, which will be used if `ext/intl` is not present, or if the developer wishes to disable translation (e.g., validators compose a translator by default, but quite often the validation messages do not need to be translated); translation can be disabled by setting the `translator` configuration key to a boolean `false`.
- [\#5469](https://github.com/zendframework/zf2/issues/5469) adds a new `AbstractConsoleController`, and logic in the `ControllerManager` for injecting the `ConsoleAdapter` object into such controllers. This abstract class tests if the incoming request is a console request, and raises an exception if not; it also provides a `getConsole()` method for access to the composed `ConsoleAdapter`.
- [\#5612](https://github.com/zendframework/zf2/issues/5612) updates `Zend\Mvc\Application::init()` to allow listeners specified in the configuration passed to the method to override those discovered during bootstrapping; in essence, application-level configuration should have more specificity than module-level configuration.
- [\#5670](https://github.com/zendframework/zf2/issues/5670) provides the ability to create a `controller_map` within `view_manager` configuration. This map allows you to do the following:
- Indicate modules that include subnamespaces in their name to include all namespace segments in the template name:     Xerkus\FooModule =>
        xerkux/foo-module/
 via the configuration `Xerkus\FooModule => true`.
- Map a specific template prefix to a given module:     ZfcUser =>
        'zf-commons/zfc-user
.

This change is opt-in, and thus backwards compatible.

- [\#5759](https://github.com/zendframework/zf2/issues/5759) adds a new method to the `FlashMessenger`, `renderCurrent()`, allowing you to render flash messages sent in the current request (using the same API as `renderMessages()`).

### Zend\\Navigation

- [\#5080](https://github.com/zendframework/zf2/issues/5080) fixes the `Breadcrumb` view helper such that it will now pass the specified separator.
- [\#5803](https://github.com/zendframework/zf2/issues/5803) hides sub menus when all pages in the sub menu are currently hidden.

### Zend\\Paginator

- [\#4427](https://github.com/zendframework/zf2/issues/4427) adds the ability to provide `$group` and `$having` clauses to a `DbTableGateway``Zend\Paginator` adapter.
- [\#5272](https://github.com/zendframework/zf2/issues/5272) adds a new `Callback` pagination adapter; the new adapter accepts two callbacks, one for returning the items, another for returning the count. The items callback will receive the requested offset and number of items per page as arguments: `function ($offset, $itemsPerPage)`.

### Zend\\Permissions\\Acl

- [\#5628](https://github.com/zendframework/zf2/issues/5628) adds a new `AssertionAggregate`, which enables two concepts: the ability to chain multiple assertions, as well as the ability to use named assertions as plugins. (The change also creates a `Zend\Permissions\Acl\Assertion\AssertionManager`, which is a plugin manager implementation).

### Zend\\ServiceManager

- A number of performance improvements were made to how abstract factories are processed and invoked.

### Zend\\Session

- [\#4995](https://github.com/zendframework/zf2/issues/4995) adds the ability to specify session validators in configuration consumed by the `SessionManagerFactory`.

### Zend\\Soap

- [\#5792](https://github.com/zendframework/zf2/issues/5792) adds a "debug mode" to `Zend\Soap\Server`. When enabled, any exception thrown is treated as a `Fault` response (vs. only those whitelisted).
- [\#5810](https://github.com/zendframework/zf2/issues/5810) adds a `getException()` method to `Zend\Soap\Server`, allowing you to retrieve the exception that caused a fault response (e.g., to log it).
- [\#5811](https://github.com/zendframework/zf2/issues/5811) creates a public `getSoap()` method in `Zend\Soap\Server` to allow you to access the composed `SoapServer` instance. This allows you to use `setReturnResponse()` and still return fault responses (which must be triggered by the `SoapServer` instance directly.)

### Zend\\Stdlib

- [\#4534](https://github.com/zendframework/zf2/issues/4534) introduces a `JsonSerializable` polyfill, to provide support for that built-in PHP interface on PHP versions prior to 5.4.
- [\#4751](https://github.com/zendframework/zf2/issues/4751) provides a new interface, `Zend\Stdlib\Hydrator\HydratorAwareInterface`, for hinting that an object composes, or can compose, a `Zend\Stdlib\Hydrator\HydratorInterface` instance. A corresponding PHP Trait is also provided.
- [\#4908](https://github.com/zendframework/zf2/issues/4908) segregates `Zend\Stdlib\Hydrator\HydratorInterface` into two separate interfaces, `Zend\Stdlib\Extractor\ExtractionInterface` and `Zend\Stdlib\Hydrator\HydrationInterfac`. The original interface has been modified to extend both of the new interfaces. This allows developers to implement one or the other behavior, based on the needs of the application. (As an example, if an application only needs to extract data for serialization, it could typehint on `Zend\Stdlib\Extractor\ExtractionInterface` only.)
- [\#5364](https://github.com/zendframework/zf2/issues/5364) adds a new subcomponent to hydrators, `NamingStrategy`. A `NamingStrategy` can be used by hydrators to determine the name to use for keys and properties when extracting and hydrating.
- [\#5365](https://github.com/zendframework/zf2/issues/5365) adds `Zend\Stdlib\Guard`, which provides traits for performing common argument type validations. For example, an object composing the `ArrayOrTraversableGuardTrait` could call `$this->guardForArrayOrTraversable($arg)` in order to validate `$arg` is an array or `Traversable`.
- [\#5380](https://github.com/zendframework/zf2/issues/5380) adds context support to hydrator strategies, allowing them to receive the object being extracted or the array being hydrated when performing their logic.
- [\#5702](https://github.com/zendframework/zf2/issues/5702) moves `Zend\Mvc\Router\PriorityList` into `Zend\Stdlib`, as it has general-purpose use cases. The former class was modified to extend the latter.

### Zend\\Test

- [\#4946](https://github.com/zendframework/zf2/issues/4946) adds two new methods to the `AbstractControllerTestCase`, `assertTemplateName()` and `assertNotTemplateName()`.
- [\#5649](https://github.com/zendframework/zf2/issues/5649) adds the `assertResponseReasonPhrase()` assertion.
- [\#5730](https://github.com/zendframework/zf2/issues/5730) adds the ability to allow session persistence when performing multiple dispatches.
- [\#5731](https://github.com/zendframework/zf2/issues/5731) adds a new argument to `dispatch()`, `$isXmlHttpRequest`; when boolean `true`, this adds an     X-Requested-With:
      XMLHttpRequest
 header to the request object.

### Zend\\Validator

- [\#4940](https://github.com/zendframework/zf2/issues/4940) adds a new validator, `Bitwise`, for performing bitwise validation operations.
- [\#5664](https://github.com/zendframework/zf2/issues/5664) removes the translation of validator message _keys_. While this is a backwards-incompatible change, this capability should never have been present, and removing it fixes a number of posted issues, as well as improves performance when retrieving validation error messages. A related change, [\#5666](https://github.com/zendframework/zf2/issues/5666), removes translation of validation error messages from `Zend\Form\View\Helper\FormElementErrors`, as translation happens within the validators themselves; this prevents double translation, and, again, improves performance.
- [\#5780](https://github.com/zendframework/zf2/issues/5780) adds the ability to set the "break chain on failure" flag via a configuration option; this allows setting the flag when using the `attachByName()` method of the `ValidatorChain`.

### Zend\\Version

- [\#4625](https://github.com/zendframework/zf2/issues/4625) adds the ability to pass a `Zend\Http\Client` to `Zend\Version\Version::getLatest()`, which should solve situations where `allow_url_fopen` is disabled.

### Zend\\View

- [\#4679](https://github.com/zendframework/zf2/issues/4679) provides the ability to specify Internet Explorer conditional stylesheets in the `HeadLink` and `HeadStyle` view helpers, conditional metadata in the `HeadMeta` view helper, and conditional scripts in the `HeadScript` view helper.
- [\#5255](https://github.com/zendframework/zf2/issues/5255) adds the ability to retrieve child view models based on what variable they registered to capture to in the parent; this is implemented via a new interface, `Zend\View\Model\RetrievableChildrenInterface`, which defines the method `getChildrenByCaptureTo()`.
- [\#5266](https://github.com/zendframework/zf2/issues/5266) attempts to make calls to `PhpRenderer::render()` slightly more robust by checking the return value from `include`ing a view script, and raising an exception when the `include` fails.

Thank You!
----------

 A big thank you to the dozens upon dozens of contributors who helped make this new feature release a reality! This was truly a community-driven effort, and would not have been possible without the contributions of each and every one of you.

Roadmap
-------

 At this time, I am proposing a bi-monthly maintenance release schedule; however, we will often release an initial ".1" maintenance version sooner. After that, however, we will schedule maintenance releases every 2 months.

 For minor (feature) releases, I am proposing every six months, giving us a September 2014 release date for 2.4.0.

 If you have opinions on the release schedule, I invite you to voice them on our [mailing lists](/archives).