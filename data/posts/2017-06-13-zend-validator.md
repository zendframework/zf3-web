---
layout: post
title: Validate input using zend-validator
date: 2017-06-13T12:05:00-05:00
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2017-06-13-zend-validator.html
categories:
- blog
- components
- zend-validator
- security

---

In our previous post, [we covered zend-filter](/blog/2017-06-08-zend-filter.html),
The filters in zend-filter are generally used to _pre-filter_ or _normalize_
incoming data. This is all well and good, but we still don't know if the data is
_valid_. That's where zend-validator comes in.

## Installation

To install zend-validator, use Composer:

```bash
$ composer require zendframework/zend-validator
```

Like zend-filter, the only _required_ dependency is zend-stdlib. However, a few other
components are suggested, based on which filters and/or features you may want to
use:

- zendframework/zend-servicemanager is used by the `ValidatorPluginManager` and
  `ValidatorChain` to look up validators by their _short name_ (versus fully
  qualified class name), as well as to allow usage of validators with
  dependencies.
- zendframework/zend-db is used by a pair of validators that can check if a
  matching record exists (or does not!).
- zendframework/zend-uri is used by the `Uri` validator.
- The CSRF validator requires both zendframework/zend-math and
  zendframework/zend-session.
- zendframework/zend-i18n and zendframework/zend-i18n-resources can be installed
  in order to provide translation of validation error messages.

For our examples, we'll be using the `ValidatorChain` functionality with a
`ValidatorPluginManager`, so we will also want to install zend-servicemanager:

```bash
$ composer require zendframework/zend-servicemanager
```

## ValidatorInterface

The current incarnation of zend-validator is _stateful_; validation error
messages are stored in the validator itself. As such, validators must implement
the `ValidatorInterface`:

```php
namespace Zend\Validator;

interface ValidatorInterface
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value);

    /**
     * @return array
     */
    public function getMessages();
}
```

The `$value` can be literally anything; a validator examines it to see if it is
valid, and returns a boolean result. If it is invalid, a subsequent call to
`getMessages()` should return an associative array with the keys being message
identifiers, and the values the human-readable message strings.

As such, usage looks like the following:

```php
if (! $validator->isValid($value)) {
    // Invalid value
    echo "Failed validation:\n";
    foreach ($validator->getMessages() as $message) {
        printf("- %s\n", $message);
    }
    return false;
}
// Valid value!
return true;
```

> ### Stateless validations are planned
>
> We are planning to rewrite the zend-validator component for its version 3
> release to be _stateless_. When we do, the `ValidatorInterface` will be
> rewritten to have `isValid()` return a `ValidationResult`. That instance will
> provide a method for determining if the validation was successful, encapsulate
> the value that was validated, and, for invalid values, provide access to the
> validation error messages. Doing so will allow better re-use of validators
> within the same execution process.

zend-validator provides a few dozen filters for common operations, including things
like:

- Common conditionals like `LessThan`, `GreaterThan`, `Identical`, `NotEmpty`,
  `IsInstanceOf`, `InArray`, and `Between`.
- String values, such as `StringLength`, `Regex`.
- Network-related values such as  `Hostname`, `Ip`, `Uri`, and `EmailAddress`.
- Business values such as `Barcode`, `CreditCard`, `GpsPoint`, `Iban`, and `Uuid`.
- Date and time related values such as `Date`, `DateStep`, and `Timezone`.

Any of these validators may be used by themselves.

In many cases, though, your validation may be related to a _set_ of validations:
as an example, the value must be non-empty, a certain number of characters, and
fulfill a regular expression. Like filters, zend-validator allows you to do this
with _chains_.

## ValidatorChain

Usage of a validator chain is similar to filter chains: attach validators you
want to execute, and then pass the value to the chain:

```php
use Zend\Validator;

$validator = new Validator\ValidatorChain();
$validator->attach(new Validator\NotEmpty());
$validator->attach(new Validator\StringLength(['min' => 6]));
$validator->attach(new Validator\Regex('/^[a-f0-9]{6,12}$/');

if (! $validator->isValid($value)) {
    // Failed validation
    var_dump($validator->getMessages());
}
```

The above uses validator instances, eliminating the need for
`ValidatorPluginManager`, and thus avoids usage of zend-servicemanager. However,
if we have zend-servicemanager installed, we can replace usage of `attach()`
with `attachByName()`:

```php
use Zend\Validator;

$validator = new Validator\ValidatorChain();
$validator->attachByName('NotEmpty');
$validator->attachByName('StringLength', ['min' => 6]);
$validator->attachByName('Regex', ['pattern' => '/^[a-f0-9]{6,12}$/']);

if (! $validator->isValid($value)) {
    // Failed validation
    var_dump($validator->getMessages());
}
```

## Breaking the chain

If you were to run either of these examples with `$value = ''`, you may discover
something unexpected: you'll get validation error messages _for every single
validator_!  This seems wasteful; there's no need to run the `StringLength` or
`Regex` validators if the value is empty, is there?

To solve this problem, when attaching a validator, we can tell the chain to
_break execution_ if the given validator fails. This is done by passing a
boolean flag:

- as the second argument to `attach()`
- as the third argument to `attachByName()` (the second argument is an array of
  constructor options)

Let's update the second example:

```php
use Zend\Validator;

$validator = new Validator\ValidatorChain();
$validator->attachByName('NotEmpty', [], true);
$validator->attachByName('StringLength', ['min' => 6], true);
$validator->attachByName('Regex', ['pattern' => '/^[a-f0-9]{6,12}$/']);

if (! $validator->isValid($value)) {
    // Failed validation
    var_dump($validator->getMessages());
}
```

The above adds a boolean `true` as the `$breakChainOnFailure` argument to the
`attachByName()` method calls of the `NotEmpty` and `StringLength` validators
(we had to provide an empty array of options for the `NotEmpty` validator so we
could pass the flag). In these cases, if the value fails validation, no further
validators will be executed.

Thus:

- `$value = ''` will result in a single validation failure message, produced by
  the `NotEmpty` validator.
- `$value = 'test'` will result in a single validation failure message, produced by
  the `StringLength` validator.
- `$value = 'testthis'` will result in a single validation failure message,
  produced by the `Regex` validator.

## Prioritization

Validators are executed in the same order in which they are attached to the
chain by default. However, internally, they are stored in a `PriorityQueue`;
this allows you to provide a specific order in which to execute the validators.
Higher values execute earlier, while lower values (including negative values)
execute last. The default priority is 1.

Priority values may be passed as the third argument to `attach()` and fourth
argument to `attachByName()`.

As an example:

```php
$validator = new Validator\ValidatorChain();
$validator->attachByName('StringLength', ['min' => 6], true, 1);
$validator->attachByName('Regex', ['pattern' => '/^[a-f0-9]{6,12}$/'], false, -100);
$validator->attachByName('NotEmpty', [], true, 100);
```

In the above, when executing the validation chain, the order will still be
`NotEmpty`, followed by `StringLength`, followed by `Regex`.

> ### Why prioritize?
>
> Why would you use this feature? The main reason is if you want to define
> validation chains via configuration, and cannot guarantee the order in which
> the items will be present in configuration. By adding a priority value, you
> can ensure that recreation of the validation chain will preserve the expected
> order.

## Context

Sometimes we may want to vary how we validate a value based on whether or not
another piece of data is present, or based on that other piece of data's value.
zend-validator offers an unofficial API for that, via an optional `$context`
value you can pass to `isValid()`. The `ValidatorChain` accepts this value, and,
if present, will pass it to each validator it composes.

As an example, let's say you want to capture an email address (form field
"contact"), but only if the user has selected a radio button allowing you to do
so (form field "allow_contact"). We might write that validator as follows:

```php
use ArrayAccess;
use ArrayObject;
use Zend\Validator\EmailAddress;
use Zend\Validator\ValidatorInterface;

class ContactEmailValidator implements ValidatorInterface
{
    const ERROR_INVALID_EMAIL = 'contact-email-invalid';

    /** @var string */
    private $contextVariable;

    /** @var EmailAddress */
    private $emailValidator;

    /** @var string[] */
    private $messages = [];

    /** @var string[] */
    private $messageTemplates = [
        self::ERROR_INVALID_EMAIL => 'Email address "%s" is invalid',
    ];

    public function __construct(
        EmailAddress $emailValidator = null,
        string $contextVariable = 'allow_contact'
    ) {
        $this->emailValidator = $emailValidator ?: new EmailAddress();
        $this->contextVariable = $contextVariable;
    }

    public function isValid($value, $context = null)
    {
        $this->messages = [];

        if (! $this->allowsContact($context)) {
            // Value will be discarded, so always valid.
            return true;
        }

        if ($this->emailValidator->isValid($value)) {
            return true;
        }

        $this->messages[self::ERROR_INVALID_EMAIL] = sprintf(
            $this->messageTemplates[self::ERROR_INVALID_EMAIL],
            var_export($value, true)
        );
        return false;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    private function allowsContact($context) : bool
    {
        if (! $context ||
            ! (is_array($context)
              || $context instanceof ArrayObject
              || $context instanceof ArrayAccess)
        ) {
            return false;
        }

        $allowsContact = $context[$this->contextVariable] ?? false;

        return (bool) $allowsContact;
    }
}
```

We would then add it to the validator chain, and call it like so:

```php
$validator->attach(new ContactEmailValidator());
if (! $validator->isValid($data['contact'], $data)) {
    // Failed validation!
}
```

This approach can allow for some quite complex validation routines, particularly
if you nest validation chains within custom validators!

## Registering your own validators.

If you write your own validators, chances are you'll want to use them with the
`ValidatorChain`. This class composes a `ValidatorPluginManager`, which is a
plugin manager built on top of zend-servicemanager. As such, you can register
your validators with it:

```php
$plugins = $validator->getPluginManager();
$plugins->setFactory(ContactEmailValidator::class, ContactEmailValidatorFactory::class);
$plugins->setService(ContactEmailValidator::class, $contactEmailValidator);
```

Alternately, if using zend-mvc or Expressive, you can provide configuration via
the `validators` configuration key:

```php
return [
    'validators' => [
        'factories' => [
            ContactEmailValidator::class => ContactEmailValidatorFactory::class,
        ],
    ],
];
```

If you want to use a "short name" to identify your validator, we recommend using
an alias, aliasing the short name to the fully qualified class name.

## Wrapping up

Between using zend-filter to normalize and pre-filter values, and zend-validator
to validate the values, you can start locking down the input your users submit
to your application.

That said, what we've demonstrated so far is how to work with single values.
Most forms submit _sets_ of values; using the approaches so far can lead to a
lot of code!

We have a solution for this as well, via our zend-inputfilter component, which
we'll be detailing in our next post!

> ## Save the date!
>
> Want to learn more about Expressive and Zend Framework? What better location
> than ZendCon 2017! ZendCon will be hosted 23-26 October 2017 in Las Vegas,
> Nevada, USA. [Visit the ZendCon website for more
> information](http://www.zendcon.com).
