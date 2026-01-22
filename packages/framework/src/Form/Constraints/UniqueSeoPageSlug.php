<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Symfony\Component\Validator\Constraint;

class UniqueSeoPageSlug extends Constraint
{
    /**
     * @param string $message
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage|null $ignoredSeoPage
     * @param int|null $domainId
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $message = 'Seo page with slug {{ pageSlug }} already exists',
        public ?SeoPage $ignoredSeoPage = null,
        public ?int $domainId = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
