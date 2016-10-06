[![Join the chat at https://gitter.im/icehawk/forms](https://badges.gitter.im/icehawk/forms.svg)](https://gitter.im/icehawk/icehawk?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Build Status](https://travis-ci.org/icehawk/forms.svg?branch=master)](https://travis-ci.org/icehawk/forms)
[![Coverage Status](https://coveralls.io/repos/github/icehawk/forms/badge.svg?branch=master)](https://coveralls.io/github/icehawk/forms?branch=master)
[![Latest Stable Version](https://poser.pugx.org/icehawk/forms/v/stable)](https://packagist.org/packages/icehawk/forms) 
[![Total Downloads](https://poser.pugx.org/icehawk/forms/downloads)](https://packagist.org/packages/icehawk/forms) 
[![Latest Unstable Version](https://poser.pugx.org/icehawk/forms/v/unstable)](https://packagist.org/packages/icehawk/forms) 
[![License](https://poser.pugx.org/icehawk/forms/license)](https://packagist.org/packages/icehawk/forms)

![IceHawk Framework](https://icehawk.github.io/images/Logo-Flying-Tail-White.png)

# IceHawk\Forms

Forms component for the [IceHawk](https://github.com/icehawk/icehawk) framework.

Provides a convenient object to store form data and feedback with CSRF protection support.

### Usage

```php
<?php declare(strict_types=1);

namespace MyVendor\MyProject;

use IceHawk\Forms\Security\Token;

# Create a new token (without expiry)
$token = new Token();

echo $token->toString(); # or echo $token;

# Create a new token (with expiry in 50 seconds)
$token = (new Token())->expiresIn( 50 );

# Create a token from string (i.e. when sent through a post form)
$token = Token::fromString( '...' );

# Compare tokens
$token   = new Token();
$other   = new Token();
$another = Token::fromString($token->toString());

$token->equals($other);   # false
$other->equals($token);   # false

$token->equals($another); # true
$other->equals($another); # true
# ...

# Check token expiry
$token = (new Token())->expiresIn( 2 );

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

use IceHawk\Forms\Feedback;

# Create a new feedback with default severity (Feedback::ERROR)
$feedback = new Feedback( 'message' );

# Create a new feedback with explicit severity
# Available severities: Feedback::ERROR (default), Feedback::WARNING, Feedback::NOTICE, Feedback::SUCCESS, Feedback::NONE 
$feedback = new Feedback( 'message', Feedback::WARNING );

# Retrieve feedback message
$message = $feedback->getMessage();

# Retrieve feedback severity
$severity = $feedback->getSeverity();
```

## Form

Aggregating object to be stored in session for securely exchanging form data and feedback between read and write side of a web application.  

### Usage

```php
<?php declare(strict_types=1);

namespace MyVendor\MyProject;

use IceHawk\Forms\Form;
use IceHawk\Forms\FormId;
use IceHawk\Forms\Feedback;
use IceHawk\Forms\Security\Token;

# Create a new instance identified by a form ID
$form = new Form( new FormId( 'myForm' ) );

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
$form->renewToken( (new Token())->expiresIn( 600 ) );

# Renew the CSRF token with own token implementation
# Token implementation must follow the \IceHawk\Forms\Interfaces\IdentifiesFormRequestSource interface
$form->renewToken( new MyToken() );

# Check if CSRF token is valid (boolean)
# Checks token string and expiry
$token = Token::fromString( $_POST['token'] );
$form->isTokenValid( $token );

# Check if CSRF token is valid (throws exceptions)
# Checks token string and expiry
# Throws \IceHawk\Forms\Excpetion\TokenMismatch, if token string does not match
# Throws \IceHawk\Forms\Excpetion\TokenHasExpired, if token has expired
$token = Token::fromString( $_POST['token'] );
$form->guardTokenIsValid( $token );

# Check if token is expired
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

# Add single feedback (using default \IceHawk\Forms\Feedback class)
# Feedback can but must not be bound to data keys
# Feedback::ERROR is the default severity
$form->addFeedback( 'username', new Feedback( 'Username is invalid', Feedback::ERROR );

# Add multiple feedbacks (using default \IceHawk\Forms\Feedback class)
$form->addFeedbacks(
    [
        'general'  => new Feedback( 'Some errors occurred.', Feedback::WARNING ),
        'username' => new Feedback( 'Username is invalid.', Feedback::ERROR ),
    ]
);

# Add feedback with own feedback implementation
# Feedback implementation must follow the \IceHawk\Forms\Interfaces\ProvidesFeedback interface
$form->addFeedback( 'firstname', new MyFeedback( 'Firstname is invalid.' ) );

# Check for feedbacks
$form->hasFeedbacks(); # true

# Check for single feedback
if ( $form->hasFeedback( 'username' ) )
{
    # Retrieve single feedback
    $usernameFeedback = $form->getFeedback( 'username' );
}

# Retrieve all feedbacks
$feedbacks = $form->getFeedbacks();

# Retrive feedbacks filtered by keys
$filteredFeedbacks = $form->getFeedbacks(
    function( ProvidesFeedback $feedback, string $key )
    {
        return ($key == 'general');  
    }
);

# Retrive feedbacks filtered by severity
$filteredFeedbacks = $form->getFeedbacks(
    function( ProvidesFeedback $feedback )
    {
        return ($feedback->getSeverity() == Feedback::ERROR);
    }
);

# Retrieve an empty feedback for a key that was not set
# Returns new Feedback( '', Feedback::NONE );
$emptyFeedback = $form->getFeedback( 'email' );

# Reset all feedbacks
$form->resetFeedbacks();

# Reset the form to initial state and renew CSRF token
$form->reset();
```
