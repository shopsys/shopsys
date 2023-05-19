<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueSlugsOnDomainsValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(protected readonly Domain $domain, protected readonly DomainRouterFactory $domainRouterFactory)
    {
    }

    /**
     * @param array $values
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueSlugsOnDomains) {
            throw new UnexpectedTypeException($constraint, UniqueSlugsOnDomains::class);
        }

        $this->validateDuplication($values, $constraint);
        $this->validateExists($values, $constraint);
    }

    /**
     * @param array $values
     * @param \Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomains $constraint
     */
    protected function validateDuplication(array $values, UniqueSlugsOnDomains $constraint)
    {
        $slugsCountByDomainId = $this->getSlugsCountIndexedByDomainId($values);

        foreach ($slugsCountByDomainId as $domainId => $countBySlug) {
            $domainConfig = $this->domain->getDomainConfigById($domainId);

            foreach ($countBySlug as $slug => $count) {
                if ($count > 1) {
                    $this->context->addViolation(
                        $constraint->messageDuplicate,
                        [
                            '{{ url }}' => $domainConfig->getUrl() . '/' . $slug,
                        ],
                    );
                }
            }
        }
    }

    /**
     * @param array $values
     * @param \Shopsys\FrameworkBundle\Form\Constraints\UniqueSlugsOnDomains $constraint
     */
    protected function validateExists($values, UniqueSlugsOnDomains $constraint)
    {
        foreach ($values as $urlData) {
            $domainId = $urlData[UrlListData::FIELD_DOMAIN];
            $domainConfig = $this->domain->getDomainConfigById($domainId);
            $slug = $urlData[UrlListData::FIELD_SLUG];

            $domainRouter = $this->domainRouterFactory->getRouter($domainId);

            try {
                $domainRouter->match('/' . $slug);
            } catch (ResourceNotFoundException $e) {
                continue;
            }

            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ url }}' => $domainConfig->getUrl() . '/' . $slug,
                ],
            );
        }
    }

    /**
     * @param array $values
     * @return int[][]
     */
    protected function getSlugsCountIndexedByDomainId(array $values)
    {
        $slugsCountByDomainId = [];

        foreach ($values as $urlData) {
            $domainId = $urlData[UrlListData::FIELD_DOMAIN];
            $slug = $urlData[UrlListData::FIELD_SLUG];

            if (!array_key_exists($domainId, $slugsCountByDomainId)) {
                $slugsCountByDomainId[$domainId] = [];
            }

            if (!array_key_exists($slug, $slugsCountByDomainId[$domainId])) {
                $slugsCountByDomainId[$domainId][$slug] = 0;
            }

            $slugsCountByDomainId[$domainId][$slug]++;
        }

        return $slugsCountByDomainId;
    }
}
