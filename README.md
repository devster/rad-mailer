RAD Mailer
==========
[![Build Status](https://travis-ci.org/devster/rad-mailer.svg)](https://travis-ci.org/devster/rad-mailer)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/devster/rad-mailer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/devster/rad-mailer/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/devster/rad-mailer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/devster/rad-mailer/?branch=master)

**Send Twig templated email with Swiftmailer at speed of light. Dead simple.**

[![](http://www.reactiongifs.com/wp-content/uploads/2011/09/mind_blown.gif)](http://www.reactiongifs.com/wp-content/uploads/2011/09/mind_blown.gif)

Installation
------------

### Composer

Add this to your composer.json

```json
{
    "require": {
        "devster/rad-mailer": "~1.0"
    }
}
```

Usage
-----

### Global usage

```php

// twig is optional, like from
$mailer = new Rad\Mailer($swiftmailer, $twig, $from = 'john@example.com');

// Send a simple email
$nbEmailsSent = $mailer->send(array(
    'from'      => 'bob@example.com', // Optional. By default the value set in the constructor.
                                      // 'bob@example.com', array('bob@example.com' => 'Bob', ...)
                                      // or an object (see more details below)
    'to'        => 'robert@example.com', // Same as from
    'subject'   => 'Hello {{name}}!', // A twig template as string or a twig file template (ex: email.html.twig)
    'body'      => 'body.html.twig', // Same as subject
    'data'      => array('name' => 'Rob'), // Optional. The data used in both templates subject and body
    'body_type' => 'text/plain' // Optional, default: text/html. 'text/html' or 'text/plain'
));

// Send a more complex email
// Create a \Swift_Message pre set with data
$message = $mailer->createMessage(array(
    'to'        => 'robert@example.com',
    'subject'   => 'Hello {{name}}!',
    'body'      => 'body.html.twig',
    'data'      => array('name' => 'Rob'),
));

$message->attach(\Swift_Attachment::fromPath('/path/to/image.jpg'));

$nbEmailsSent = $mailer->sendMessage($message);
```

This library is aims to work with symfony, so you can pass an object as `from` and `to` option.
This object must has:

    * an `email` public property
    * or `mail` public property
    * or a `getEmail` public method
    * or a `getMail` public method
    * and a `name` public property
    * or a `getName` public method
    * or a `__toString` public method

Or you can extends the `getDataFromObject` method from the `Rad\Mailer`.

### Symfony

Register the mailer as service

```yaml
services:
    rad_mailer:
        class: Rad\Mailer
        arguments: [@mailer, @twig, 'rob@example.com']
```

Why not a bundle? Because its overkill. period.

### Silex

```php
$app = new \Silex\Application;

$app->register(new \Silex\Provider\SwiftmailerServiceProvider, ...);
$app->register(new \Silex\Provider\TwigServiceProvider, ...);

$app->register(new \Rad\Silex\MailerServiceProvider, array(
    'rad_mailer.from'  => 'rob@example.com', // Optional
    'rad_mailer.class' => 'MyMailer\That\Extends\Rad\Mailer', // Optional. By default 'Rad\Mailer' of course
));

$app['rad_mailer']->send(array(...));
```

License
-------

This plugin is licensed under the DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
