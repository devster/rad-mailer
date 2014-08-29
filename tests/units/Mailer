<?php

namespace Rad\Tests\Units;

require_once __DIR__.'/../User.php';

use Rad\Mailer as M;
use \Swift_Mailer;

class Mailer extends \mageekguy\atoum\test
{
    protected function getTwig()
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../template');
        $twig   = new \Twig_Environment($loader);

        return $twig;
    }

    protected function createMailer($noTwig = false)
    {
        $this->mockGenerator->orphanize('__construct');
        $sm = new \mock\Swift_Mailer;
        $this->calling($sm)->send = function ($message = null) {
            return $message;
        };

        $twig = $noTwig ? null : $this->getTwig();

        $mailer = new M(
            $sm,
            $twig,
            'test@example.com'
        );

        return $mailer;
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
        $mailer       = $this->createMailer();
        $noTwigMailer = $this->createMailer(true);

        $this
            ->string($mailer->renderTwig($template, $data))
                ->isEqualTo($result)
            ->string($noTwigMailer->renderTwig($template, $data))
                ->isEqualTo($template)
        ;
    }

    public function testsetFrom()
    {
        $mailer = $this->createMailer();

        $from = array('bob@example' => 'bob');

        $this
            ->string($mailer->getFrom())
                ->isEqualTo('test@example.com')
            ->if($mailer->setFrom($from))
            ->then
                ->array($mailer->getFrom())
                    ->isEqualTo($from)
        ;
    }

    public function getUserData()
    {
        $o = new \StdClass;
        $o->email = 'bob@example.com';
        $o->name = 'bob';

        $u = new \StdClass;
        $u->email = 'bob@example.com';

        return array(
            array('bob@example.com', 'bob@example.com'),
            array($u, 'bob@example.com'),
            array(array('bob@example.com' => 'bob'), array('bob@example.com' => 'bob')),
            array($o, array('bob@example.com' => 'bob')),
            array(new \User, array('bob@example.com' => 'bob')),
        );
    }

    /**
     * @dataProvider getUserData
     */
    public function testGuessData($data, $result)
    {
        $mailer = $this->createMailer();

        $this
            ->variable($mailer->guessData($data))
                ->isEqualTo($result)
        ;
    }

    public function testSendMessage()
    {
        $mailer = $this->createMailer();

        $m = $mailer->createMessage();
        $this
            ->if($r = $mailer->sendMessage($m))
            ->then
                ->mock($mailer->getMailer())
                    ->call('send')
                        ->once()
                ->object($r)
                    ->isEqualTo($m)
        ;
    }

    protected function getSendOptions()
    {
        return array(
            // pass
            array(
                false,
                array(
                    'to'      => 'me@example.com',
                    'subject' => 'Hello {{ name }}',
                    'body'    => 'hello.html.twig',
                    'data'    => array('name' => 'bob')
                ),
                array(
                    'to'        => 'me@example.com',
                    'from'      => 'test@example.com',
                    'subject'   => 'Hello bob',
                    'body'      => "Hello bob!\n",
                    'body_type' => 'text/html'
                )
            ),
            array(
                false,
                array(
                    'to'        => 'me@example.com',
                    'from'      => new \User,
                    'subject'   => 'hello.html.twig',
                    'body'      => 'Bouyakasha {{name }}',
                    'body_type' => 'text/plain',
                    'data'      => array('name' => 'bob')
                ),
                array(
                    'to'        => 'me@example.com',
                    'from'      => array('bob@example.com' => 'bob'),
                    'subject'   => "Hello bob!\n",
                    'body'      => 'Bouyakasha bob',
                    'body_type' => 'text/plain'
                )
            ),
            array(
                false,
                array(
                    'to'        => 'me@example.com',
                    'subject'   => 'hello',
                    'body'      => 'Bouyakasha',
                ),
                array(
                    'to'        => 'me@example.com',
                    'from'      => 'test@example.com',
                    'subject'   => "hello",
                    'body'      => 'Bouyakasha',
                    'body_type' => 'text/html'
                )
            ),
            // fail
            // missing to
            array(
                true,
                array(
                    'subject'   => 'hello',
                    'body'      => 'Bouyakasha',
                ),
                array()
            ),
            // missing subject
            array(
                true,
                array(
                    'to'   => 'hello',
                    'body' => 'Bouyakasha',
                ),
                array()
            ),
            // missing body
            array(
                true,
                array(
                    'to'      => 'hello',
                    'subject' => 'Bouyakasha',
                ),
                array()
            ),
            // bad body content type
            array(
                true,
                array(
                    'to'        => 'hello@test.com',
                    'subject'   => 'Bouyakasha',
                    'body'      => 'Bouyakasha',
                    'body_type' => 'flex',
                ),
                array()
            ),
        );
    }

    /**
     * @dataProvider getSendOptions
     */
    public function testCreateMessage($throwException, array $data, array $excepted)
    {
        $mailer = $this->createMailer();

        if ($throwException) {
            $this
                ->exception(function () use ($mailer, $data) {
                    $mailer->createMessage($data);
                })
            ;
        } else {

            if (is_string($excepted['to'])) {
                $excepted['to'] = array($excepted['to'] => null);
            }

            if (is_string($excepted['from'])) {
                $excepted['from'] = array($excepted['from'] => null);
            }

            $this
                ->if($m = $mailer->createMessage($data))
                ->then
                    ->string($m->getSubject())
                        ->isEqualTo($excepted['subject'])
                    ->string($m->getBody())
                        ->isEqualTo($excepted['body'])
                    ->array($m->getTo())
                        ->isEqualTo($excepted['to'])
                    ->array($m->getFrom())
                        ->isEqualTo($excepted['from'])
                    ->string($m->getContentType())
                        ->isEqualTo($excepted['body_type'])
            ;
        }
    }

    /**
     * @dataProvider getSendOptions
     */
    public function testSend($throwException, array $data, array $excepted)
    {
        $mailer = $this->createMailer();

        if ($throwException) {
            $this
                ->exception(function () use ($mailer, $data) {
                    $mailer->send($data);
                })
            ;
        } else {

            if (is_string($excepted['to'])) {
                $excepted['to'] = array($excepted['to'] => null);
            }

            if (is_string($excepted['from'])) {
                $excepted['from'] = array($excepted['from'] => null);
            }

            $this
                ->if($m = $mailer->send($data))
                ->then
                    ->mock($mailer->getMailer())
                        ->call('send')
                            ->once()
                    ->string($m->getSubject())
                            ->isEqualTo($excepted['subject'])
                    ->string($m->getBody())
                        ->isEqualTo($excepted['body'])
                    ->array($m->getTo())
                        ->isEqualTo($excepted['to'])
                    ->array($m->getFrom())
                        ->isEqualTo($excepted['from'])
                    ->string($m->getContentType())
                        ->isEqualTo($excepted['body_type'])
            ;
        }
    }
}
