# CHANGELOG

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com).

## [2.0.0] - YYYY-MM-DD

[2.0.0]: https://github.com/icehawk/forms/compare/v1.0.0...v2.0.0

* Switched from Vagrant to docker-compose development environment, see [Contribution Guide](./.github/CONTRIBUTING.md)

### Backwards incompatible changes (BC breaks)

* The library requires at least PHP version 8.0.0 or higher

* The class `IceHawk\Forms\Form` has been declared `final`

* The class `IceHawk\Forms\Feedback` has been moved to `IceHawk\Forms\Feedbacks\Feedback`

* Common object constructors have been replaced by named constructors. All `__construct` methods have be
  declared `final private`.

* Parameter and return type declarations have been added to all methods, if possible.

| Old                                   | New                                            |
|---------------------------------------|------------------------------------------------|
| `new Form( new FormId('myFormId') )`  | `Form::new( FormId::new('myFormId') )`         |
| `new Token()`                         | `Token::new()`                                 |
| `(new Token())->expiresIn( 2 )`       | `Token::newWithExpiry(2)`                      |
| `new Feedback('Message')`             | `Feedback::new('key', 'Message', 'severity')`  |

* All interfaces have been renamed and aligned with the implementation were needed

| Old                                         | New                                         |
|---------------------------------------------|---------------------------------------------|
| `IceHawk\Forms\Identifies`                  | `[removed]`                                 |
| `IceHawk\Forms\IdentifiesForm`              | `IceHawk\Forms\FormIdInteface`              |
| `IceHawk\Forms\IdentifiesFormRequestSource` | `IceHawk\Forms\TokenInterface`              |
| `IceHawk\Forms\ProvidesFeedback`            | `IceHawk\Forms\FeedbackInterface`           |
| `[new]`                                     | `IceHawk\Forms\FeedbackCollectionInterface` |
| `IceHawk\Forms\ProvidesFormData`            | `IceHawk\Forms\FormInterface`               |

* The `\Stringable` interface was explicitly added to
    * `IceHawk\Forms\Interfaces\FormIdInterface`
    * `IceHawk\Forms\Interfaces\TokenInterface`

* The exception `IceHawk\Forms\Exceptions\InvalidFormIdException` has been added.

* The suffix `Exception` has been added to all exception class names.

| Old                                              | New                                                       |
|--------------------------------------------------|-----------------------------------------------------------|
| `IceHawk\Forms\Exceptions\FormsException`        | `[removed]`                                               |
| `[new]`                                          | `IceHawk\Forms\Exceptions\InvalidFormIdException`         |
| `IceHawk\Forms\Exceptions\InvalidExpiryInterval` | `IceHawk\Forms\Exceptions\InvalidExpiryIntervalException` |
| `IceHawk\Forms\Exceptions\InvalidTokenString`    | `IceHawk\Forms\Exceptions\InvalidTokenStringException`    |
| `IceHawk\Forms\Exceptions\TokenHasExpired`       | `IceHawk\Forms\Exceptions\TokenHasExpiredException`       |
| `IceHawk\Forms\Exceptions\TokenMismatch`         | `IceHawk\Forms\Exceptions\TokenMismatchException`         |

* The base exception `IceHawk\Forms\Exceptions\FormsException` has been removed.  
  All exceptions extend apropriate SPL exceptions now.

| Library exception                                         | extends SPL exception       |
|-----------------------------------------------------------|-----------------------------|
| `IceHawk\Forms\Exceptions\InvalidFormIdException`         | `\InvalidArgumentException` |
| `IceHawk\Forms\Exceptions\InvalidExpiryIntervalException` | `\InvalidArgumentException` |
| `IceHawk\Forms\Exceptions\InvalidTokenStringException`    | `\InvalidArgumentException` |
| `IceHawk\Forms\Exceptions\TokenHasExpiredException`       | `\RuntimeException`         |
| `IceHawk\Forms\Exceptions\TokenMismatchException`         | `\RuntimeException`         |

* The value of a `FormId` instance can no longer be an empty string. This will throw
  an `IceHawk\Forms\Exceptions\InvalidFormIdException` now.

* All form feedbacks are represented by a new collection class `IceHawk\Forms\Feedbacks\Feedbacks`. Therefor some
  feedback related methods have been moved from the `Form` class to the Feedback collection.

| Old                                                    | New                                                                              |
|--------------------------------------------------------|----------------------------------------------------------------------------------|
| `$form->hasFeedback( 'some-key' )`                     | `$form->getFeedbacks()->has( 'some-key' )`                                       |
| `$form->getFeedback( 'some-key' ) : FeedbackInterface` | `$form->getFeedbacks()->get( 'some-key' ) : FeedbackCollectionInterface`         |
| `$form->getFeedbacks( ...some filter... ) : array`     | `$form->getFeedbacks()->filter(...some filter...): FeedbackCollectionInterface`  |

* The string key related to a feedback is now part of the feedback itself:

| Old                                                                 | New                                                                               |
|---------------------------------------------------------------------|-----------------------------------------------------------------------------------|
| `$form->addFeedback('some-key', new Feedback('Message'))`           | `$form->addFeedbacks(Feedback::new('some-key', 'Message', 'severity'))`           |
| `$form->addFeedbacks(['some-key' => new Feedback('Message'), ...])` | `$form->addFeedbacks(Feedback::new('some-key', 'Message', 'severity'), ...)`      |

* The method `Form#addFeedbacks()` now expects a variadic number of Feedback instances (at least one), instead of an
  assoc array with Feedback instances.

```php
public function addFeedbacks(FeedbackInterface $feedback, FeedbackInterface ...$feedbacks) : void
```

* It is possible to add more than one feedback for a specific key now.

```php
use IceHawk\Forms\Feedbacks\Feedback;use IceHawk\Forms\Form;
use IceHawk\Forms\FormId;

$form = Form::new(FormId::new('myFormId'));
$form->addFeedbacks(
    Feedback::new('some-key', 'Message 1', 'low'),
    Feedback::new('some-key', 'Message 2', 'high'),
    Feedback::new('another-key', 'Message 3', 'normal'),
);
```

## [1.0.0] - 2016-10-07

[1.0.0]: https://github.com/icehawk/forms/tree/v1.0.0

- First stable release

