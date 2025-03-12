<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueSeoPageSlugValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade $seoPageFacade
     */
    public function __construct(
        protected readonly SeoPageFacade $seoPageFacade,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueSeoPageSlug) {
            throw new UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        $pageSlug = (string)$value;

        $seoPageSlug = $this->seoPageFacade->findByDomainIdAndPageSlug($constraint->domainId, $pageSlug);

        if ($constraint->ignoredSeoPage !== $seoPageSlug) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ pageSlug }}' => $pageSlug,
                ],
            );
        }
    }
}
