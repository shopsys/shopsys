<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlSlugNormalizer;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
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
     * @param array<int, array<string, string>> $newUrlsData
     */
    #[Override]
    public function validate(mixed $newUrlsData, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueSlugsOnDomain) {
            throw new UnexpectedTypeException($constraint, UniqueSlugsOnDomain::class);
        }

        $this->validateDuplication($newUrlsData, $constraint);
        $this->validateExists($newUrlsData, $constraint);
    }

    /**
     * @param array<int, array<string, string>> $newUrlsData
     */
    protected function validateDuplication(array $newUrlsData, UniqueSlugsOnDomain $constraint): void
    {
        $domainUrl = $this->domain->getDomainConfigById($constraint->domainId)->getUrl();

        foreach ($this->getCountIndexedBySlug($newUrlsData) as $slug => $count) {
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
     * @param array<int, array<string, string>> $newUrlsData
     */
    protected function validateExists(array $newUrlsData, UniqueSlugsOnDomain $constraint): void
    {
        $domainUrl = $this->domain->getDomainConfigById($constraint->domainId)->getUrl();
        $domainRouter = $this->domainRouterFactory->getRouter($constraint->domainId);

        foreach ($newUrlsData as $urlData) {
            $slug = FriendlyUrlSlugNormalizer::normalize((string)$urlData[UrlListData::FIELD_SLUG]);

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
     * @param array<int, array<string, string>> $newUrlsData
     * @return array<string, int>
     */
    protected function getCountIndexedBySlug(array $newUrlsData): array
    {
        $countBySlug = [];

        foreach ($newUrlsData as $urlData) {
            $slug = FriendlyUrlSlugNormalizer::normalize((string)$urlData[UrlListData::FIELD_SLUG]);
            $countBySlug[$slug] = ($countBySlug[$slug] ?? 0) + 1;
        }

        return $countBySlug;
    }
}
