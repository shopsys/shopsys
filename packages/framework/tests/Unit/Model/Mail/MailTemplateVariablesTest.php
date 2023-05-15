<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Mail;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Mail\Exception\InvalidMailTemplateVariablesConfigurationException;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables;

class MailTemplateVariablesTest extends TestCase
{
    private const INVALID_VALUE = 1234;

    public function testValidWithoutRequired(): void
    {
        $mailTemplateConfiguration = new MailTemplateVariables('Template');

        $mailTemplateConfiguration->addVariable('{variable1}', 'Variable 1', MailTemplateVariables::CONTEXT_BODY);
        $mailTemplateConfiguration->addVariable('{variable2}', 'Variable 2', MailTemplateVariables::CONTEXT_BOTH);
        $mailTemplateConfiguration->addVariable('{variable3}', 'Variable 3', MailTemplateVariables::CONTEXT_SUBJECT);

        $this->assertEquals(['{variable1}', '{variable2}'], $mailTemplateConfiguration->getBodyVariables());
        $this->assertEquals(['{variable2}', '{variable3}'], $mailTemplateConfiguration->getSubjectVariables());

        $this->assertEquals([], $mailTemplateConfiguration->getRequiredBodyVariables());
        $this->assertEquals([], $mailTemplateConfiguration->getRequiredSubjectVariables());

        $this->assertEquals(
            [
                '{variable1}' => 'Variable 1',
                '{variable2}' => 'Variable 2',
                '{variable3}' => 'Variable 3',
            ],
            $mailTemplateConfiguration->getLabeledVariables(),
        );
    }

    public function testInvalidContext(): void
    {
        $mailTemplateConfiguration = new MailTemplateVariables('Template');

        $this->expectException(InvalidMailTemplateVariablesConfigurationException::class);
        $this->expectExceptionMessage('Variable can be used only in body or subject');

        $mailTemplateConfiguration->addVariable('{variable}', 'Variable', self::INVALID_VALUE);
    }

    public function testRequiredInSubject(): void
    {
        $mailTemplateConfiguration = new MailTemplateVariables('Template');

        $mailTemplateConfiguration->addVariable(
            '{variable1}',
            'Variable',
            MailTemplateVariables::CONTEXT_SUBJECT,
            MailTemplateVariables::REQUIRED_SUBJECT,
        );

        $this->assertEquals(['{variable1}'], $mailTemplateConfiguration->getRequiredSubjectVariables());
        $this->assertEquals([], $mailTemplateConfiguration->getRequiredBodyVariables());

        $this->expectException(InvalidMailTemplateVariablesConfigurationException::class);
        $mailTemplateConfiguration->addVariable(
            '{variable2}',
            'Variable',
            MailTemplateVariables::CONTEXT_SUBJECT,
            MailTemplateVariables::REQUIRED_BODY,
        );
        $mailTemplateConfiguration->addVariable(
            '{variable3}',
            'Variable',
            MailTemplateVariables::CONTEXT_SUBJECT,
            MailTemplateVariables::REQUIRED_BOTH,
        );
        $mailTemplateConfiguration->addVariable(
            '{variable4}',
            'Variable',
            MailTemplateVariables::CONTEXT_SUBJECT,
            self::INVALID_VALUE,
        );
    }

    public function testRequiredInBody(): void
    {
        $mailTemplateConfiguration = new MailTemplateVariables('Template');

        $mailTemplateConfiguration->addVariable(
            '{variable1}',
            'Variable',
            MailTemplateVariables::CONTEXT_BODY,
            MailTemplateVariables::REQUIRED_BODY,
        );
        $this->assertEquals(['{variable1}'], $mailTemplateConfiguration->getRequiredBodyVariables());
        $this->assertEquals([], $mailTemplateConfiguration->getRequiredSubjectVariables());

        $this->expectException(InvalidMailTemplateVariablesConfigurationException::class);
        $mailTemplateConfiguration->addVariable(
            '{variable2}',
            'Variable',
            MailTemplateVariables::CONTEXT_BODY,
            MailTemplateVariables::REQUIRED_SUBJECT,
        );
        $mailTemplateConfiguration->addVariable(
            '{variable3}',
            'Variable',
            MailTemplateVariables::CONTEXT_BODY,
            MailTemplateVariables::REQUIRED_BOTH,
        );
        $mailTemplateConfiguration->addVariable(
            '{variable4}',
            'Variable',
            MailTemplateVariables::CONTEXT_BODY,
            self::INVALID_VALUE,
        );
    }

    public function testRequiredInBoth(): void
    {
        $mailTemplateConfiguration = new MailTemplateVariables('Template');

        $mailTemplateConfiguration->addVariable(
            '{variable1}',
            'Variable',
            MailTemplateVariables::CONTEXT_BOTH,
            MailTemplateVariables::REQUIRED_BOTH,
        );
        $mailTemplateConfiguration->addVariable(
            '{variable2}',
            'Variable',
            MailTemplateVariables::CONTEXT_BOTH,
            MailTemplateVariables::REQUIRED_BODY,
        );
        $mailTemplateConfiguration->addVariable(
            '{variable3}',
            'Variable',
            MailTemplateVariables::CONTEXT_BOTH,
            MailTemplateVariables::REQUIRED_SUBJECT,
        );

        $this->assertEquals(['{variable1}', '{variable2}'], $mailTemplateConfiguration->getRequiredBodyVariables());
        $this->assertEquals(['{variable1}', '{variable3}'], $mailTemplateConfiguration->getRequiredSubjectVariables());

        $this->expectException(InvalidMailTemplateVariablesConfigurationException::class);
        $mailTemplateConfiguration->addVariable(
            '{variable4}',
            'Variable',
            MailTemplateVariables::CONTEXT_BOTH,
            self::INVALID_VALUE,
        );
    }
}
