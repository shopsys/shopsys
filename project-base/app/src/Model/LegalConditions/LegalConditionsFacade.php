<?php

declare(strict_types=1);

namespace App\Model\LegalConditions;

use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade as BaseLegalConditionsFacade;

/**
 * @method \App\Model\Article\Article|null findTermsAndConditions(int $domainId)
 * @method setTermsAndConditions(int $domainId, \App\Model\Article\Article|null $termsAndConditions = null)
 * @method \App\Model\Article\Article|null findPrivacyPolicy(int $domainId)
 * @method setPrivacyPolicy(int $domainId, \App\Model\Article\Article|null $privacyPolicy = null)
 * @method bool isArticleUsedAsLegalConditions(\App\Model\Article\Article $article)
 * @method \App\Model\Article\Article|null findArticle(string $settingKey, int $domainId)
 * @method setArticle(string $settingKey, int $domainId, \App\Model\Article\Article|null $article = null)
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
