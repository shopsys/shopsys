<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Model\Mail\Exception\InvalidMailTemplateVariablesConfigurationException;

class MailTemplateVariables
{
    /**
     * Variable will be replaced in email body
     */
    public const CONTEXT_BODY = 1;
    /**
     * Variable will be replaced in email subject
     */
    public const CONTEXT_SUBJECT = 2;
    /**
     * Variable will be replaced in email body and subject
     */
    public const CONTEXT_BOTH = 3;
    /**
     * Variable is not required
     */
    public const REQUIRED_NOWHERE = 0;
    /**
     * Variable is required in email body
     */
    public const REQUIRED_BODY = 1;
    /**
     * Variable is required in email subject
     */
    public const REQUIRED_SUBJECT = 2;
    /**
     * Variable is required in email body and subject
     */
    public const REQUIRED_BOTH = 3;

    /**
     * @var string[]
     */
    protected array $variables = [];

    /**
     * @var string[]
     */
    protected array $bodyVariables = [];

    /**
     * @var string[]
     */
    protected array $subjectVariables = [];

    /**
     * @var string[]
     */
    protected array $requiredBodyVariables = [];

    /**
     * @var string[]
     */
    protected array $requiredSubjectVariables = [];

    /**
     * @param string $readableName
     * @param string|null $type
     */
    public function __construct(protected string $readableName, protected readonly ?string $type = null)
    {
    }

    /**
     * @param string $readableName
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    public function withNewName(string $readableName): self
    {
        $clone = clone $this;

        $clone->readableName = $readableName;

        return $clone;
    }

    /**
     * @param string $variable
     * @param string $label
     * @param int $context one of CONTEXT_* constants
     * @param int $required one of REQUIRED_* constants
     * @return $this
     */
    public function addVariable(string $variable, string $label, int $context = self::CONTEXT_BOTH, int $required = self::REQUIRED_NOWHERE): self
    {
        if (array_key_exists($variable, $this->variables)) {
            throw new InvalidMailTemplateVariablesConfigurationException(
                sprintf('Variable "%s" is already registered.', $variable),
            );
        }

        $this->variables[$variable] = $label;
        $this->addVariableToSections($variable, $context);
        $this->addVariableToRequiredSections($variable, $context, $required);

        return $this;
    }

    /**
     * @param string $variable
     * @param int $context
     */
    protected function addVariableToSections(string $variable, int $context): void
    {
        switch ($context) {
            case self::CONTEXT_BOTH:
                $this->bodyVariables[] = $variable;
                $this->subjectVariables[] = $variable;

                break;
            case self::CONTEXT_BODY:
                $this->bodyVariables[] = $variable;

                break;
            case self::CONTEXT_SUBJECT:
                $this->subjectVariables[] = $variable;

                break;
            default:
                throw new InvalidMailTemplateVariablesConfigurationException(
                    'Variable can be used only in body or subject',
                );
        }
    }

    /**
     * @param string $variable
     * @param int $context
     * @param int $required
     */
    protected function addVariableToRequiredSections(string $variable, int $context, int $required): void
    {
        switch ($required) {
            case self::REQUIRED_NOWHERE:
                break;
            case self::REQUIRED_BOTH:
                if ($context !== self::CONTEXT_BOTH) {
                    throw new InvalidMailTemplateVariablesConfigurationException(
                        'Variable have to be in body and subject to make it required in both',
                    );
                }

                $this->requiredBodyVariables[] = $variable;
                $this->requiredSubjectVariables[] = $variable;

                break;
            case self::REQUIRED_BODY:
                if ($context === self::CONTEXT_SUBJECT) {
                    throw new InvalidMailTemplateVariablesConfigurationException(
                        'Variable have to be present in body to make it required there',
                    );
                }

                $this->requiredBodyVariables[] = $variable;

                break;
            case self::REQUIRED_SUBJECT:
                if ($context === self::CONTEXT_BODY) {
                    throw new InvalidMailTemplateVariablesConfigurationException(
                        'Variable have to be present in subject to make it required there',
                    );
                }
                $this->requiredSubjectVariables[] = $variable;

                break;
            default:
                throw new InvalidMailTemplateVariablesConfigurationException(
                    'Variable can be required only in body, subject or nowhere',
                );
        }
    }

    /**
     * @return string
     */
    public function getReadableName(): string
    {
        return $this->readableName;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getSubjectVariables(): array
    {
        return $this->subjectVariables;
    }

    /**
     * @return string[]
     */
    public function getRequiredSubjectVariables(): array
    {
        return $this->requiredSubjectVariables;
    }

    /**
     * @return string[]
     */
    public function getBodyVariables(): array
    {
        return $this->bodyVariables;
    }

    /**
     * @return string[]
     */
    public function getRequiredBodyVariables(): array
    {
        return $this->requiredBodyVariables;
    }

    /**
     * @return array
     */
    public function getLabeledVariables(): array
    {
        return $this->variables;
    }
}
