<?php

namespace Rad\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Rad Mailer Provider.
 */
class MailerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['rad_mailer.class'] = 'Rad\Mailer';
        $app['rad_mailer.from']  = null;

        $app['rad_mailer'] = $app->share(function () use ($app) {
            $mailerClass = $app['rad_mailer.class'];

            if (!isset($app['mailer']) || !$app['mailer'] instanceof \Swift_Mailer) {
                throw new \LogicException('The `mailer` service must be set and an instance of \Swift_Mailer. See SwiftmailerServiceProvider');
            }

            $twig = null;

            if (isset($app['twig']) && $app['twig'] instanceof \Twig_Environment) {
                $twig = $app['twig'];
            }

            return new $mailerClass($app['mailer'], $twig, $app['rad_mailer.from']);
        });
    }

    public function boot(Application $app)
    {
    }
}
