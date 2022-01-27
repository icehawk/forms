[![QA](https://github.com/icehawk/forms/actions/workflows/qa.yml/badge.svg)](https://github.com/icehawk/forms/actions/workflows/qa.yml)
[![Latest Stable Version](https://poser.pugx.org/icehawk/forms/v/stable)](https://packagist.org/packages/icehawk/forms)
[![Total Downloads](https://poser.pugx.org/icehawk/forms/downloads)](https://packagist.org/packages/icehawk/forms)
[![License](https://poser.pugx.org/icehawk/forms/license)](https://packagist.org/packages/icehawk/forms)

![IceHawk Framework](https://icehawk.github.io/images/Logo-Flying-Tail-White.png)

# IceHawk\Forms

Forms component for the [IceHawk](https://github.com/icehawk/icehawk) framework.

Provides a convenient object to store form data and feedback with CSRF protection support.

## Token

### Usage

```php
<?php declare(strict_types=1);

namespace MyVendor\MyProject;

use IceHawk\Forms\Security\Token;

# Create a new token (without expiry)
$token = Token::new();

echo $token->toString(); # or echo $token;

# Create a new token (with expiry in 50 seconds)
$token = Token::newWithExpiry( 50 );

# Create a token from string (i.e. when sent through a post form)
$token = Token::fromString( '...' );

# Compare tokens
$token   = Token::new();
$other   = Token::new();
$another = Token::fromString($token->toString());

$token->equals($other);   # false
$other->equals($token);   # false

$token->equals($another); # true
$other->equals($another); # true
# ...

# Check token expiry
$token = Token::newWithExpiry(2);

$token->isExpired(); # false

sleep(3);

$token->isExpired(); # true
```

## Feedback

Simple value object for form feedback with severity.

### Usage

```php
<?php declare(strict_types=1);

namespace MyVendor\MyProject;

use IceHawk\Forms\Feedbacks\Feedback;

# Create a new feedback message for a key with low severity
# Keys usually refer to input fields or form groups
$feedback = Feedback::new( 'key', 'message', 'low' );

# Create a new feedback message for another key with high severity
$feedback = Feedback::new( 'another-key', 'message', 'high' );

# Retrieve feedback key
$key = $feedback->getKey();

# Retrieve feedback message
$message = $feedback->getMessage();

# Retrieve feedback severity
$severity = $feedback->getSeverity();
```

## Form

Aggregating object to be stored in session for securely exchanging form data and feedback between read and write side of
a web application.

### Usage

```php
<?php declare(strict_types=1);

namespace MyVendor\MyProject;

use IceHawk\Forms\Form;
use IceHawk\Forms\FormId;
use IceHawk\Forms\Feedbacks\Feedback;
use IceHawk\Forms\Interfaces\FeedbackInterface;
use IceHawk\Forms\Security\Token;

# Create a new instance identified by a form ID
$form = Form::new( FormId::new( 'myForm' ) );

# Create a new instance identified by your own form ID
# Form-ID implementations must follow the \IceHawk\Forms\Interfaces\FormIdInterface
# or extend the FormId class
final class MyFormId extends FormId
{
    # Named constructors for each form ID is a good practice for example
    public static function myForm() : self
    {
        return new self('myForm');
    }
}

$form = Form::new( MyFormId::myForm() );

# Check if form has already data set
if ( $form->wasDataSet() )
{
    # Set up some default data (single)
    $form->set( 'username', 'johndoe' );
    
    # Set up some default data (multiple)
    $form->setData(
        [
            'firstname' => 'John',
            'lastname'  => 'Doe',
        ]
    );
}

# Retrieve the initially set CSRF token
$token = $form->getToken();

# Renew the CSRF token (Default Token class is used, see above)
$form->renewToken();

# Renew the CSRF token with expiry in 10 minutes
$form->renewToken( Token::newWithExpiry( 600 ) );

# Renew the CSRF token with your own token implementation
# Token implementations must follow the \IceHawk\Forms\Interfaces\TokenInterface
$form->renewToken( new MyToken() );

# Check if CSRF token is valid (boolean)
# Checks token string and expiry
$token = Token::fromString( $_POST['token'] );
$form->isTokenValid( $token );

# Check if CSRF token is valid (throws exceptions)
# Checks token string and expiry
# Throws \IceHawk\Forms\Excpetion\TokenMismatchException, if token string does not match
# Throws \IceHawk\Forms\Excpetion\TokenHasExpiredException, if token has expired
$token = Token::fromString( $_POST['token'] );
$form->guardTokenIsValid( $token );

# Check if token has expired
$form->hasTokenExpired();

# Check if a key isset
$form->isset( 'username' );

# Retrieve single value
# Returns NULL, if the key was not set
$username = $form->get( 'username' );

# Retrieve all values incl. keys (assoc. array)
$formData = $form->getData();

# Unset a value
$form->unset( 'username' );

# Add single feedback (using default \IceHawk\Forms\Feedbacks\Feedback class)
# Feedback can but must not be bound to data keys
$form->addFeedbacks( Feedback::new( 'username', 'Username is invalid', 'error' ) );

# Add multiple feedbacks (using default \IceHawk\Forms\Feedbacks\Feedbacks\Feedback class)
# You can add multiple feedbacks for the same key
$form->addFeedbacks(
    Feedback::new( 'general', 'Some errors occurred', 'hint' ),
    Feedback::new( 'username', 'Username is invalid', 'error' ),
    Feedback::new( 'username', 'Username must have at least 3 characters', 'hint' ),
);

# Add feedback with your own feedback implementation
# Feedback implementations must follow the \IceHawk\Forms\Interfaces\FeedbackInterface
# or extend the default Feedback class
final class MyFeedback extends Feedback
{
    # Named constructors for each severity is a good practice for example
    public static function invalid(string $key, string $message) : self
    {
        return new self($key, $message, 'invalid');
    }
}

$form->addFeedbacks( MyFeedback::invalid( 'firstname', 'Firstname is invalid.' ) );

# Check for any feedbacks
$form->hasFeedbacks();

# Check for feedbacks for a specific key
if ( $form->getFeedbacks()->has( 'username' ) )
{
    # Retrieve first feedback for a specific key
    $usernameFeedback = $form->getFeedbacks()->getFirst( 'username' );
    
    # Retrieve all feedbacks for a specific key
    $usernameFeedbacks = $form->getFeedbacks()->get( 'username' );
}

# Retrieve all feedbacks
$feedbacks = $form->getFeedbacks();

# Retrive feedbacks filtered by severity
$filteredFeedbacks = $form->getFeedbacks()->filter(
    static fn(FeedbackInterface $fb): bool => $fb->getSeverity() === 'low'
);

# Get an array of all message strings
$messages = $form->getFeedbacks()->map(
    static fn( FeedbackInterface $fb ): string => $fb->getMessage()
);

# Reset all feedbacks
$form->resetFeedbacks();

# Reset the form to initial state and renew CSRF token
$form->reset();
```
