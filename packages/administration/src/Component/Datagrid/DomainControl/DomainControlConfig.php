<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\DomainControl;

use Webmozart\Assert\Assert;

/**
 * Configuration of the domain control of a datagrid, resolved into a
 * `\Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope` per request.
 */
final readonly class DomainControlConfig
{
    /**
     * @param int[]|null $allowedDomainIds Domain IDs available in the filter. Null allows all domains available to the administrator.
     * @param string|null $filterNamespace The namespace the filter remembers its selection under, required by the filter. Pass the same value in two datagrids to share the selection between them.
     * @param string|null $domainIdPath Path to the domain ID the records are limited by, for an entity related to a domain in another way than by implementing `DomainSeparatedEntityInterface` — `domains.domainId`. Null takes `domainId` of an entity implementing it.
     */
    public function __construct(
        public DomainControlType $type = DomainControlType::NONE,
        public ?array $allowedDomainIds = null,
        public ?string $filterNamespace = null,
        public ?string $domainIdPath = null,
    ) {
        if ($this->type === DomainControlType::NONE) {
            Assert::null($this->domainIdPath, 'A path to the domain is only supported by a domain control.');
        }

        if ($this->type === DomainControlType::FILTER) {
            Assert::notNull($this->filterNamespace, 'The domain filter requires the namespace it remembers its selection under.');
            Assert::allInteger($this->allowedDomainIds ?? []);

            return;
        }

        Assert::null($this->allowedDomainIds, 'Allowed domain IDs are only supported by the domain filter.');
        Assert::null($this->filterNamespace, 'Filter namespace is only supported by the domain filter.');
    }

    public function isEnabled(): bool
    {
        return $this->type !== DomainControlType::NONE;
    }
}
