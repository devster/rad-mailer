<?php

namespace Rad\Silex\Tests\Units;

class MailerServiceProvider extends \mageekguy\atoum\test
{
    protected function createApplication($noTwig = false, $noSm = false)
    {
        $app = new \Silex\Application;

        $this->mockGenerator->orphanize('__construct');
        $sm = new \mock\Swift_Mailer;
        $this->calling($sm)->send = function ($message = null) {
            return $message;
        };

        $app->register(new \Rad\Silex\MailerServiceProvider, array(
            'rad_mailer.from' => 'bob@example.com'
        ));

        if (!$noTwig) {
            $app->register(new \Silex\Provider\TwigServiceProvider, array(
                'twig.path' => __DIR__.'/../../template'
            ));
        }

        if (!$noSm) {
            $app['mailer'] = $sm;
        }

        $app->boot();

        return $app;
    }

    public function testRegister()
    {
        $app     = $this->createApplication();
        $appNoSm = $this->createApplication(false, true);

        $this
            ->object($m = $app['rad_mailer'])
                ->isInstanceOf('Rad\Mailer')
            ->exception(function () use ($appNoSm) {
                $appNoSm['rad_mailer'];
            })
                ->isInstanceOf('\LogicException')
                ->message
                    ->contains('The `mailer` service must be set')
        ;
    }

    public function renderTwigProvider()
    {
        return array(
            array('Hello bob', array(), 'Hello bob'),
            array('Hello {{ name }}', array('name' => 'bob'), 'Hello bob'),
            array('hello.html.twig', array('name' => 'bob'), "Hello bob!\n")
        );
    }

    /**
     * @dataProvider renderTwigProvider
     */
    public function testRenderTwig($template, $data, $result)
    {
        $app    = $this->createApplication();
        $mailer = $app['rad_mailer'];

        $app2         = $this->createApplication(true);
        $noTwigMailer = $app2['rad_mailer'];

        $this
            ->string($mailer->renderTwig($template, $data))
                ->isEqualTo($result)
            ->string($noTwigMailer->renderTwig($template, $data))
                ->isEqualTo($template)
        ;
    }

    public function testGetFrom()
    {
        $app = $this->createApplication();

        $this
            ->string($app['rad_mailer']->getFrom())
                ->isEqualTo('bob@example.com')
        ;
    }
}
