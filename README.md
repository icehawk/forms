[![Build Status](https://travis-ci.org/icehawk/forms.svg?branch=master)](https://travis-ci.org/icehawk/forms)
[![Coverage Status](https://coveralls.io/repos/github/icehawk/forms/badge.svg?branch=master)](https://coveralls.io/github/icehawk/forms?branch=master)
[![Latest Stable Version](https://poser.pugx.org/icehawk/forms/v/stable)](https://packagist.org/packages/icehawk/forms) 
[![Total Downloads](https://poser.pugx.org/icehawk/forms/downloads)](https://packagist.org/packages/icehawk/forms) 
[![Latest Unstable Version](https://poser.pugx.org/icehawk/forms/v/unstable)](https://packagist.org/packages/icehawk/forms) 
[![License](https://poser.pugx.org/icehawk/forms/license)](https://packagist.org/packages/icehawk/forms)

# IceHawk\Forms

Forms component for the IceHawk framework

## Security\Token

This is a default token implementation for CSRF protection.

### Usage

```php
<?php declare(strict_types=1);

namespace Vendor\Project;

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

## Form structure

```
- Form::new(id)
 |- Id
 |- Token
 |- Data
 `- Feedbacks
```