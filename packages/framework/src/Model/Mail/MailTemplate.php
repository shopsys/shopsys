<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="mail_templates",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="name_domain", columns={"name", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class MailTemplate
{
    const REGISTRATION_CONFIRM_NAME = 'registration_confirm';
    const RESET_PASSWORD_NAME = 'reset_password';
    const PERSONAL_DATA_ACCESS_NAME = 'personal_data_access';
    const PERSONAL_DATA_EXPORT_NAME = 'personal_data_export';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $bccEmail;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $subject;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $body;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $sendMail;

    public function __construct(string $name, int $domainId, MailTemplateData $mailTemplateData)
    {
        $this->name = $name;
        $this->domainId = $domainId;
        $this->edit($mailTemplateData);
    }

    public function edit(MailTemplateData $mailTemplateData): void
    {
        $this->bccEmail = $mailTemplateData->bccEmail;
        $this->subject = $mailTemplateData->subject;
        $this->body = $mailTemplateData->body;
        $this->sendMail = $mailTemplateData->sendMail;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getBccEmail(): ?string
    {
        return $this->bccEmail;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function isSendMail(): bool
    {
        return $this->sendMail;
    }
}
