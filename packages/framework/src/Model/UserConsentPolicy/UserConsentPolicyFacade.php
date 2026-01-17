<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\UserConsentPolicy;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\HttpFoundation\RequestStack;

class UserConsentPolicyFacade
{
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly Setting $setting,
        protected readonly Domain $domain,
        protected readonly RequestStack $requestStack,
    ) {
    }

    public function findUserConsentPolicyArticleByDomainId(int $domainId): ?Article
    {
        $userConsentPolicyArticleId = $this->setting->getForDomain(Setting::USER_CONSENT_POLICY_ARTICLE_ID, $domainId);

        if ($userConsentPolicyArticleId === null) {
            return null;
        }

        return $this->articleFacade->findById(
            $this->setting->getForDomain(Setting::USER_CONSENT_POLICY_ARTICLE_ID, $domainId),
        );
    }

    public function setUserConsentPolicyArticleOnDomain(?Article $userConsentPolicyArticle, int $domainId): void
    {
        $this->setting->setForDomain(
            Setting::USER_CONSENT_POLICY_ARTICLE_ID,
            $userConsentPolicyArticle?->getId(),
            $domainId,
        );
    }

    public function isArticleUsedAsUserConsentPolicyArticle(Article $article): bool
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if ($this->findUserConsentPolicyArticleByDomainId($domainConfig->getId()) === $article) {
                return true;
            }
        }

        return false;
    }
}
