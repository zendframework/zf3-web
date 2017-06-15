---
layout: post
title: Validate data using zend-inputfilter
date: 2017-06-15T11:15:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-06-15-zend-inputfilter.html
categories:
- blog
- components
- zend-inputfilter
- zend-filter
- zend-validator
- security

---

In our previous two posts, we covered [zend-filter](/blog/2017-06-08-zend-filter.html)
and [zend-validator](/blog/2017-06-13-zend-validator.html). With these two
components, you now have the tools necessary to ensure any given user input is
valid, fulfilling the first half of the "filter input, escape output" mantra.

However, as we discussed in the zend-validator article, as powerful as
validation chains are, they only allow you to validate a _single_ value at a
time. How do you go about validating _sets_ of values &mdash; such as data
submitted from a form, or a resource for an API?

To solve that problem, Zend Framework provides
[zend-inputfilter](https://docs.zendframework.com/zend-inputfilter). An _input
filter_ aggregates one or more _inputs_, any one of which may also be another
input filter, allowing you to validate complex, multi-set, and nested set values.

## Installation

To install zend-inputfilter, use Composer:

```bash
$ composer require zendframework/zend-inputfilter
```

zend-inputfilter only directly requires zend-filter, zend-stdlib, and
zend-validator. To use its powerful factory feature, however, you'll also need
zend-servicemanager, as it greatly simplifies creation of input filters:

```bash
$ composer require zendframework/zend-servicemanager
```

## Theory of operation

An input filter composes one or more inputs, any of which may also be an input
filter (and thus represent a _set_ of data values).

Any given input is considered _required_ by default, but can be configured to be
_optional_. When required, an input will be considered invalid if the value is
not present in the data set, or is empty. When optional, if the value is not
present, or is empty, it is considered valid. An additional flag, `allow_empty`,
can be used to allow empty values for required elements; still another flag,
`continue_if_empty`, will force validation to occur for either required or
optional values if the value is present but empty.

When validating a value, two steps occur:

- The value is passed to a _filter chain_ in order to normalize the value.
  Typical normalizations include stripping non-digit characters for phone
  numbers and credit card numbers; trimming whitespace; etc.
- The value is then passed to a _validator chain_ to determine if the normalized
  value is valid.

An input filter aggregates the inputs, as well as the _values_ themselves. You
pass the user input to the input filter after it has been configured, and then
check to see if it is valid. If it is, you can pull the _normalized_ values from
it (as well as the raw values, if desired). If any value is invalid, you would
then pull the validation error messages from it.

> ### Stateless operation
>
> The current approach is _stateful_: values are passed to the input filter
> before you execute its `isValid()` method, and then the values and any
> validation error messages are stored within the input filter instance for
> later retrieval. This can cause issues if you wish to use the same input
> filter multiple times.
>
> For this reason, the planned version 3 release will be _stateless_: calling
> `isValid()` will require passing the value(s) to validate, and both inputs
> and input filters alike will return a result object from this method with
> the raw and normalized values, the result of validation, and any validation
> error messages.

## Getting started

Let's consider a registration form where we want to capture a user email and
their password. In our first example, we will use explicit usage, which does not
require the use of plugin managers.

```php
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator;

$email = new Input('email');
$email->getFilterChain()
		->attach(new Filter\StringTrim());
$email->getValidatorChain()
		->attach(new Validator\EmailAddress());

$password = new Input('password');
$password->getValidatorChain()
		->attach(new Validator\StringLength(8), true)
	  ->attach(new Validator\Regex('/[a-z]/'))
	  ->attach(new Validator\Regex('/[A-Z]/'))
	  ->attach(new Validator\Regex('/[0-9]/'))
	  ->attach(new Validator\Regex('/[.!@#$%^&*;:]/'));

$inputFilter = new InputFilter();
$inputFilter->add($email);
$inputFilter->add($password);
$inputFilter->setData($_POST);

if ($inputFilter->isValid()) {
    echo "The form is valid\n";
    $values = $inputFilter->getValues();
} else {
    echo "The form is not valid\n";
    foreach ($inputFilter->getInvalidInput() as $error) {
        var_dump($error->getMessages());
    }
}
```

The above creates two inputs, one each for the incoming email address and
password. The email address will be _trimmed_ of whitespace, and then validated.
The password will be validated only, checking that we have a value of at least 8
characters, with at least one each of lowercase, uppercase, digit, and special
characters. Further, if any given character is missing, we'll get a validation
error message so that the user knows how to create their password.

Each input is added to an input filter instance. We pass the form data (via the
`$_POST` superglobal), and then check to see if it is valid. If so, we grab the
values from it (we can get the original values via `getRawValues()`). If not, we
grab error messages from it.

By default, all inputs are considered _required_. Let's say we also wanted to
collect the user's full name, but make that optional. We could create an input
like the following:

```php
$name = new Input('user_name');
$name->setRequired(false);             // OPTIONAL!
$name>getFilterChain()
		->attach(new Filter\StringTrim());
```

## Input specifications

As noted in the "Installation" section, we can leverage zend-servicemanager and
the various plugin managers composed in it in order to create our filters.

`Zend\InputFilter\InputFilter` internally composes `Zend\InputFilter\Factory`,
which itself composes:

- `Zend\InputFilter\InputFilterPluginManager`, a plugin manager for managing
  `Zend\InputFilter\Input` and `Zend\InputFilter\InputFilter` instances.
- `Zend\Filter\FilterPluginManager`, a plugin manager for filters.
- `Zend\Validator\ValidatorPluginManager`, a plugin manager for validators.

The upshot is that we can often use _specifications_ instead of _instances_ to
create our inputs and input filters.

As such, our above examples can be written like this:

```php
use Zend\InputFilter\InputFilter;

$inputFilter = new InputFilter();
$inputFilter->add([
    'name' => 'email',
    'filters' => [
        ['name' => 'StringTrim']
    ],
    'validators' => [
        ['name' => 'EmailAddress']
    ],
]);
$inputFilter->add([
    'name' => 'user_name',
    'required' => false,
    'filters' => [
        ['name' => 'StringTrim']
    ],
]);
$inputFilter->add([
    'name' => 'password',
    'validators' => [
        [
            'name' => 'StringLength',
            'options' => ['min' => 8],
            'break_chain_on_failure' => true,
        ],
        ['name' => 'Regex', 'options' => ['pattern' => '/[a-z]/'],
        ['name' => 'Regex', 'options' => ['pattern' => '/[A-Z]/'],
        ['name' => 'Regex', 'options' => ['pattern' => '/[0-9]/'],
        ['name' => 'Regex', 'options' => ['pattern' => '/[.!@#$%^&*;:]/'],
    ],
]);
```

There are a number of other fields you could use:

- `type` allows you to specify the input or input filter class to use when
  creating the input.
- `error_message` allows you to specify a single error message to return for an
  input on validation failure. This is often useful as otherwise you'll get an
  array of messages for each input.
- `allow_empty` and `continue_if_empty`, which were discussed earlier, and
  control how validation occurs when empty values are encountered.

Why would you do this instead of using the programmatic interface, though?

First, this approach leverages the various plugin managers, which means that any
given input, input filter, filter, or validator will be pulled from their
respective plugin manager. This allows you to provide additional types easily,
but, more importantly, override existing types.

Second, the configuration-based approach allows you to store the definitions in
configuration, and potentially even _override_ the definitions via configuration
merging! Apigility utilizes this feature heavily.

### Managing the plugin managers

To ensure that you can use already configured plugin managers, you can inject
them into the `Zend\InputFilter\Factory` composed in your input filter. As an
example, considering the following service factory for an input filter:

```php
function (ContainerInterface $container)
{
    $filters = $container->get('FilterManager');
    $validators = $container->get('ValidatorManager');
    $inputFilters = $container->get('InputFilterManager');

    $inputFilter = new InputFilter();
    $inputFilterFactory = $inputFilter->getFactory();
    $inputFilterFactory->setDefaultFilterChain($filters);
    $inputFilterFactory->setDefaultValidatorChain($validators);
    $inputFilterFactory->setInputFilterManager($inputFilters);

    // add inputs to the $inputFilter, and finally return it...
    return $inputFilter;
}
```

## Managing Input Filters

The `InputFilterPluginManager` allows you to define input filters with
dependencies, which gives you the ability to create re-usable, complex input
filters. One key aspect to using this feature is that the
`InputFilterPluginManager` also ensures the configured filter and validator
plugin managers are injected in the factory used by the input filter, ensuring
any overrides or custom filters and validators you've defined are present.

To make this work, the base `InputFilter` implementation also implements
`Zend\Stdlib\InitializableInterface`, which defines an `init()` method; the
`InputFilterPluginManager` calls this _after_ instantiating your input filter
and injecting it with a factory composing all the various plugin manager
services.

What this means is that if you use this method to `add()` your inputs and nested
input filters, everything will be properly configured!

As an example, let's say we have a "transaction_id" field, and that we need to
check if that transaction identifier exists in the database. As such, we may
have a custom validator that depends on a database connection to do this. We
could write our input filter as follows:

```php
namespace MyBusiness;

use Zend\InputFilter\InputFilter;

class OrderInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => 'transaction_id',
            'validators' => [
                ['name' => TransactionIdValidator::class],
            ],
        ]);
    }
}
```

We would then register this in our `input_filters` configuration:

```php
// in config/autoload/input_filters.global.php
return [
    'input_filters' => [
        'invokables' => [
            MyBusiness\OrderInputFilter::class => MyBusiness\OrderInputFilter::class,
        ],
    ],
    'validators' => [
        'factories' => [
            MyBusiness\TransactionIdValidator::class => MyBusiness\TransactionIdValidatorFactory::class,
        ],
    ],
];
```

This approach works best with the _specification_ form; otherwise you need to
pull the various plugin managers from the composed factory and pass them to the
individual inputs:

```php
$transId = new Input();
$transId->getValidatorChain()
    ->setValidatorManager($this->getFactory()->getValidatorManager());
$transId->getValidatorChain()
    ->attach(TransactionIdValidator::class);
```

## Specification-driven input filters

Finally, we can look at _specification-driven_ input filters.

The component provides an `InputFilterAbstractServiceFactory`. When you request
an input filter or input that is not directly in the `InputFilterPluginManager`,
this abstract factory will then check to see if a corresponding value is present
in the `input_filter_specs` configuration array. If so, it will pass that
specification to a `Zend\InputFilter\Factory` configured with the various plugin
managers in order to create the instance.

Using our original example, we could define the registration form input filter
as follows:

```php
return [
    'input_filter_specs' => [
        'registration_form' => [
            [
                'name' => 'email',
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'EmailAddress']
                ],
            ],
            [
                'name' => 'user_name',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
            ],
            [
                'name' => 'password',
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => ['min' => 8],
                        'break_chain_on_failure' => true,
                    ],
                    ['name' => 'Regex', 'options' => ['pattern' => '/[a-z]/'],
                    ['name' => 'Regex', 'options' => ['pattern' => '/[A-Z]/'],
                    ['name' => 'Regex', 'options' => ['pattern' => '/[0-9]/'],
                    ['name' => 'Regex', 'options' => ['pattern' => '/[.!@#$%^&*;:]/'],
                ],
            ],
        ],
    ],
];
```

We would then retrieve it from the input filter plugin manager:

```php
$inputFilter = $inputFilters->get('registration_form');
```

Considering most input filters do not need to compose dependencies other than
the inputs and input filters they aggregate, this approach makes for a dynamic
way to define input validation.

## Topics not covered

zend-inputfilter has a ton of other features as well:

- Input and input filter _merging_.
- Handling of array values.
- Collections (repeated data sets of the same structure).
- Filtering of file uploads.

On top of all this, it provides a number of interfaces against which you can
program in order to write completely custom functionality!

One huge strength of zend-inputfilter is that it can be used for any sort of
data set you need to validate: forms, obviously, but also API payloads, data
retrieved from a message queue, and more. Let us know what you use it for!

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).

