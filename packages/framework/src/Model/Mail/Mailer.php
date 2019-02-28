<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpKernel\KernelInterface;

class Mailer
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @var \Swift_Mailer
     */
    protected $swiftMailer;

    /**
     * @param \Swift_Mailer $swiftMailer
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function __construct(Swift_Mailer $swiftMailer, KernelInterface $kernel)
    {
        $this->swiftMailer = $swiftMailer;
        $this->kernel = $kernel;
    }

    public function flushSpoolQueue()
    {
        $container = $this->kernel->getContainer();
        if (!$container->has('mailer')) {
            return;
        }
        $mailers = array_keys($container->getParameter('swiftmailer.mailers'));
        foreach ($mailers as $name) {
            if (method_exists($container, 'initialized') ? $container->initialized(sprintf('swiftmailer.mailer.%s', $name)) : true) {
                if ($container->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name))) {
                    $mailer = $container->get(sprintf('swiftmailer.mailer.%s', $name));
                    $transport = $mailer->getTransport();
                    if ($transport instanceof \Swift_Transport_SpoolTransport) {
                        $spool = $transport->getSpool();
                        if ($spool instanceof \Swift_MemorySpool) {
                            try {
                                $spool->flushQueue($container->get(sprintf('swiftmailer.mailer.%s.transport.real', $name)));
                            } catch (\Swift_TransportException $exception) {
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MessageData $messageData
     */
    public function send(MessageData $messageData)
    {
        $message = $this->getMessageWithReplacedVariables($messageData);
        $failedRecipients = [];

        if ($messageData->body === null || $messageData->subject === null) {
            throw new \Shopsys\FrameworkBundle\Model\Mail\Exception\EmptyMailException();
        }

        $successSend = $this->swiftMailer->send($message, $failedRecipients);
        if (!$successSend && count($failedRecipients) > 0) {
            throw new \Shopsys\FrameworkBundle\Model\Mail\Exception\SendMailFailedException($failedRecipients);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MessageData $messageData
     * @return \Swift_Message
     */
    protected function getMessageWithReplacedVariables(MessageData $messageData)
    {
        $toEmail = $messageData->toEmail;
        $body = $this->replaceVariables(
            $messageData->body,
            $messageData->variablesReplacementsForBody
        );
        $subject = $this->replaceVariables(
            $messageData->subject,
            $messageData->variablesReplacementsForSubject
        );
        $fromEmail = $messageData->fromEmail;
        $fromName = $messageData->fromName;

        $message = new Swift_Message();
        $message->setSubject($subject);
        $message->setFrom($fromEmail, $fromName);
        $message->setTo($toEmail);
        if ($messageData->bccEmail !== null) {
            $message->addBcc($messageData->bccEmail);
        }
        if ($messageData->replyTo !== null) {
            $message->addReplyTo($messageData->replyTo);
        }
        $message->setContentType('text/plain; charset=UTF-8');
        $message->setBody(strip_tags($body), 'text/plain');
        $message->addPart($body, 'text/html');
        foreach ($messageData->attachmentsFilepaths as $attachmentFilepath) {
            $message->attach(Swift_Attachment::fromPath($attachmentFilepath));
        }

        return $message;
    }

    /**
     * @param string $string
     * @param array $variablesKeysAndValues
     * @return string
     */
    protected function replaceVariables($string, $variablesKeysAndValues)
    {
        return strtr($string, $variablesKeysAndValues);
    }
}
