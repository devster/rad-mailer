<?php

namespace Rad;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Simple Mailer
 *
 * Provide shortcuts to send email with templating
 */
class Mailer
{
    /**
     * @var \Twig_Environment templating
     */
    protected $twig;

    /**
     * @var \Swift_Mailer mailer
     */
    protected $mailer;

    /**
     * @var object|array|string|null
     */
    protected $from;

    /**
     * Constructor.
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig = null, $from = null)
    {
        $this->mailer = $mailer;
        $this->from   = $from;

        // configure twig to template string
        if ($twig) {
            $this->twig   = clone $twig;
            $loader = new \Twig_Loader_Chain(array($this->twig->getLoader(), new \Twig_Loader_String));
            $this->twig->setLoader($loader);
        }
    }

    /**
     * Create and initialize a message
     *
     * @param  object|array|string|null $fromUser
     * @param  object|array|string|null $toUser
     *
     * @return \Swift_Message
     */
    public function createMessage($fromUser = null, $toUser = null)
    {
        $message = \Swift_Message::newInstance();

        if ($fromUser) {
            $message->setFrom($this->guessData($fromUser));
        }

        if ($toUser) {
            $message->setTo($this->guessData($toUser));
        }

        return $message;
    }

    /**
     * Get from
     *
     * @return object|array|string|null
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Get the mailer
     *
     * @return \Swift_Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * Guess data from mixed var
     *
     * @param  mixed $mixed
     *
     * @return array|string|null
     */
    public function guessData($mixed)
    {
        if (is_object($mixed)) {
            return $this->getDataFromObject($mixed);
        }

        return $mixed;
    }

    /**
     * Render a twig file template or a string template
     *
     * @param  string $template
     * @param  array  $data
     *
     * @return string
     */
    public function renderTwig($template, $data = array())
    {
        if ($this->twig) {
            return $this->twig->render($template, $data);
        }

        return $template;
    }

    /**
     * Shortcut to send an email
     *
     * @param array $options See self::getSendOptionsResolver for more details
     *
     * @return integer
     */
    public function send(array $options)
    {
        $options = $this->getSendOptionsResolver()->resolve($options);

        $message = $this->createMessage($options['from'], $options['to']);
        $message
            ->setSubject($this->renderTwig($options['subject'], $options['data']))
            ->setBody($this->renderTwig($options['body'], $options['data']), $options['body_type'])
        ;

        return $this->sendMessage($message);
    }

    /**
     * Send a message
     *
     * @param  \Swift_Message $message
     *
     * @return integer
     */
    public function sendMessage(\Swift_Message $message)
    {
        return $this->mailer->send($message);
    }

    /**
     * Set from
     *
     * @param object|array|string|null $from
     *
     * @return Mailer
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get email => name data from an unknown object
     *
     * Extends it to fit to your objects
     *
     * Duck typing powered magic
     *
     * @param mixed $object
     *
     * @return array
     */
    protected function getDataFromObject($object)
    {
        $email = $this->renderTwig('{% if o.email is defined %}{{ o.email }}{% endif %}', array('o' => $object));

        if (!$email) {
            $email = $this->renderTwig('{% if o.mail is defined %}{{ o.mail }}{% endif %}', array('o' => $object));
        }

        if (!$email) {
            throw new \Exception(sprintf('Email cannot be found on this object %s', get_class($object)));
        }

        $name = $this->renderTwig('{% if o.name is defined %}{{ o.name }}{% endif %}', array('o' => $object));

        if (method_exists($object, '__toString') && !$name) {
            $name = (string) $object;
        }

        return $name ? array($email => (string) $name) : $email;
    }

    /**
     * Create a options resolver for the send shortcut method
     *
     * @return OptionsResolver
     */
    protected function getSendOptionsResolver()
    {
        $resolver = new OptionsResolver;
        $resolver
            ->setRequired(array(
                'to',
                'subject',
                'body'
            ))
            ->setOptional(array(
                'from',
                'data',
                'body_type'
            ))
            ->setDefaults(array(
                'from'      =>  $this->getFrom(),
                'data'      => array(),
                'body_type' => 'text/html'
            ))
            ->setAllowedTypes(array(
                'from'    => array('object', 'array', 'string', 'null'),
                'to'      => array('object', 'array', 'string'),
                'subject' => 'string',
                'body'    => 'string',
                'data'    => 'array'
            ))
            ->setAllowedValues(array(
                'body_type' => array('text/html', 'text/plain')
            ))
        ;

        return $resolver;
    }
}
