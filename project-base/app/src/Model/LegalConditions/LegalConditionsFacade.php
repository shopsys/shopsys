<?php

declare(strict_types=1);

namespace App\Model\LegalConditions;

use Override;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade as BaseLegalConditionsFacade;

/**
 * @method \App\Model\Article\Article|null findTermsAndConditions(int $domainId)
 * @method void setTermsAndConditions(int $domainId, \App\Model\Article\Article|null $termsAndConditions = null)
 * @method \App\Model\Article\Article|null findPrivacyPolicy(int $domainId)
 * @method void setPrivacyPolicy(int $domainId, \App\Model\Article\Article|null $privacyPolicy = null)
 * @method bool isArticleUsedAsLegalConditions(\App\Model\Article\Article $article)
 * @method \App\Model\Article\Article|null findArticle(string $settingKey, int $domainId)
 * @method void setArticle(string $settingKey, int $domainId, \App\Model\Article\Article|null $article = null)
 * @property \App\Component\Setting\Setting $setting
 * @method __construct(\Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 */
class LegalConditionsFacade extends BaseLegalConditionsFacade
{
    #[Override]
    public function getTermsAndConditionsDownloadFilename(): string
    {
        return t('Terms-and-conditions.html');
    }
}
