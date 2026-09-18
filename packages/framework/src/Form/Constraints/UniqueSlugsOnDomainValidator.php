<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlSlugNormalizer;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueSlugsOnDomainValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param string[] $newSlugs
     */
    #[Override]
    public function validate(mixed $newSlugs, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueSlugsOnDomain) {
            throw new UnexpectedTypeException($constraint, UniqueSlugsOnDomain::class);
        }

        $this->validateDuplication($newSlugs, $constraint);
        $this->validateExists($newSlugs, $constraint);
    }

    /**
     * @param string[] $newSlugs
     */
    protected function validateDuplication(array $newSlugs, UniqueSlugsOnDomain $constraint): void
    {
        $domainUrl = $this->domain->getDomainConfigById($constraint->domainId)->getUrl();

        foreach ($this->getCountIndexedBySlug($newSlugs) as $slug => $count) {
            if ($count > 1) {
                $this->context->addViolation(
                    $constraint->messageDuplicate,
                    [
                        '{{ url }}' => $domainUrl . '/' . $slug,
                    ],
                );
            }
        }
    }

    /**
     * @param string[] $newSlugs
     */
    protected function validateExists(array $newSlugs, UniqueSlugsOnDomain $constraint): void
    {
        $domainUrl = $this->domain->getDomainConfigById($constraint->domainId)->getUrl();
        $domainRouter = $this->domainRouterFactory->getRouter($constraint->domainId);

        foreach ($newSlugs as $newSlug) {
            $slug = ExtendedClassNameResolver::resolve(FriendlyUrlSlugNormalizer::class)::normalize((string)$newSlug);

            try {
                $domainRouter->match('/' . $slug);
            } catch (ResourceNotFoundException $e) {
                continue;
            }

            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ url }}' => $domainUrl . '/' . $slug,
                ],
            );
        }
    }

    /**
     * @param string[] $newSlugs
     * @return array<string, int>
     */
    protected function getCountIndexedBySlug(array $newSlugs): array
    {
        $countBySlug = [];

        foreach ($newSlugs as $newSlug) {
            $slug = ExtendedClassNameResolver::resolve(FriendlyUrlSlugNormalizer::class)::normalize((string)$newSlug);
            $countBySlug[$slug] = ($countBySlug[$slug] ?? 0) + 1;
        }

        return $countBySlug;
    }
}
