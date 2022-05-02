<?php

declare(strict_types=1);

namespace App\Model\LegalConditions;

use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade as BaseLegalConditionsFacade;

/**
 * @method \App\Model\Article\Article|null findTermsAndConditions(int $domainId)
 * @method setTermsAndConditions(\App\Model\Article\Article|null $termsAndConditions = null, int $domainId)
 * @method \App\Model\Article\Article|null findPrivacyPolicy(int $domainId)
 * @method setPrivacyPolicy(\App\Model\Article\Article|null $privacyPolicy = null, int $domainId)
 * @method bool isArticleUsedAsLegalConditions(\App\Model\Article\Article $article)
 * @method \App\Model\Article\Article|null findArticle(string $settingKey, int $domainId)
 * @method setArticle(string $settingKey, \App\Model\Article\Article|null $privacyPolicy = null, int $domainId)
 */
class LegalConditionsFacade extends BaseLegalConditionsFacade
{
    /**
     * @return string
     */
    public function getTermsAndConditionsDownloadFilename(): string
    {
        return t('Terms-and-conditions.html');
    }
}
