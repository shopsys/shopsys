<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Override;
use Symfony\Component\Mime\Email as BaseEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

class Email extends BaseEmail
{
    /**
     * @var array<string, mixed>
     */
    protected array $metadata = [];

    public function __construct(
        protected readonly int $domainId,
        ?Headers $headers = null,
        ?AbstractPart $body = null,
    ) {
        parent::__construct($headers, $body);
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function setMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getMetadata(string $key): mixed
    {
        return $this->metadata[$key] ?? null;
    }

    /**
     * @internal
     */
    #[Override]
    public function __serialize(): array
    {
        return [$this->domainId, $this->metadata, parent::__serialize()];
    }

    /**
     * @internal
     */
    #[Override]
    public function __unserialize(array $data): void
    {
        if (count($data) === 2) {
            [$this->domainId, $parentData] = $data;
        } else {
            [$this->domainId, $this->metadata, $parentData] = $data;
        }

        parent::__unserialize($parentData);
    }
}
