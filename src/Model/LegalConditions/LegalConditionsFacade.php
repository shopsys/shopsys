<?php

declare(strict_types=1);

namespace App\Model\LegalConditions;

use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade as BaseLegalConditionsFacade;

/**
 * @property \App\Model\Article\ArticleFacade $articleFacade
 * @property \App\Component\Setting\Setting $setting
 * @method __construct(\App\Model\Article\ArticleFacade $articleFacade, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @method \App\Model\Article\Article|null findTermsAndConditions(int $domainId)
 * @method setTermsAndConditions(\App\Model\Article\Article|null $termsAndConditions, int $domainId)
 * @method \App\Model\Article\Article|null findPrivacyPolicy(int $domainId)
 * @method setPrivacyPolicy(\App\Model\Article\Article|null $privacyPolicy, int $domainId)
 * @method bool isArticleUsedAsLegalConditions(\App\Model\Article\Article $article)
 * @method \App\Model\Article\Article|null findArticle(string $settingKey, int $domainId)
 * @method setArticle(string $settingKey, \App\Model\Article\Article|null $privacyPolicy, int $domainId)
 */
class LegalConditionsFacade extends BaseLegalConditionsFacade
{
    /**
     * @return string
     */
    public function getTermsAndConditionsDownloadFilename()
    {
        return t('Terms-and-conditions.html');
    }
}
