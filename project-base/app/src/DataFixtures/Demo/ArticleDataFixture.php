<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

class ArticleDataFixture extends AbstractReferenceFixture
{
    public const string ARTICLE_TERMS_AND_CONDITIONS = 'article_terms_and_conditions';
    public const string ARTICLE_PRIVACY_POLICY = 'article_privacy_policy';
    public const string USER_CONSENT_POLICY_ARTICLE = 'article_user_consent_policy';

    private const string ATTRIBUTE_NAME_KEY = 'name';
    private const string ATTRIBUTE_PLAIN_NAME_KEY = 'plainName';
    private const string ATTRIBUTE_TEXT_KEY = 'text';
    private const string ATTRIBUTE_PLACEMENT_KEY = 'placement';
    private const string ATTRIBUTE_SEO_H1_KEY = 'seoH1';
    private const string ATTRIBUTE_SEO_TITLE_KEY = 'soeTitle';
    private const string ATTRIBUTE_SEO_META_DESCRIPTION_KEY = 'soeMetaDescription';
    private const string REFERENCE_NAME_KEY = 'referenceName';
    private const string UUID_NAMESPACE = '008cf1fb-218e-45c2-ae6e-02f9324948ba';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory $articleDataFactory
     */
    public function __construct(
        private readonly ArticleFacade $articleFacade,
        private readonly ArticleDataFactoryInterface $articleDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $data = $this->getDataForArticles($domainConfig);
            $this->createArticlesFromArray($data, $domainConfig->getId());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string[][]
     */
    private function getDataForArticles(DomainConfig $domainConfig): array
    {
        $locale = $domainConfig->getLocale();

        return [
            [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'About us',
                self::ATTRIBUTE_NAME_KEY => t('About us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Job at Shopsys',
                self::ATTRIBUTE_NAME_KEY => t('Job at Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Cooperation',
                self::ATTRIBUTE_NAME_KEY => t('Cooperation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'For press',
                self::ATTRIBUTE_NAME_KEY => t('For press', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Contacts',
                self::ATTRIBUTE_NAME_KEY => t('Contacts', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Goods care',
                self::ATTRIBUTE_NAME_KEY => t('Goods care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Installment plan',
                self::ATTRIBUTE_NAME_KEY => t('Installment plan', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Complaint',
                self::ATTRIBUTE_NAME_KEY => t('Complaint', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Frequently Asked Questions FAQ',
                self::ATTRIBUTE_NAME_KEY => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Transport and payment',
                self::ATTRIBUTE_NAME_KEY => t('Transport and payment', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Withdrawal from contract',
                self::ATTRIBUTE_NAME_KEY => t('Withdrawal from contract', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Terms and conditions of eshop',
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Where to find us',
                self::ATTRIBUTE_NAME_KEY => t('Where to find us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Department stores services',
                self::ATTRIBUTE_NAME_KEY => t('Department stores services', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Terms and conditions of department stores',
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
                self::REFERENCE_NAME_KEY => self::ARTICLE_TERMS_AND_CONDITIONS,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Privacy policy',
                self::ATTRIBUTE_NAME_KEY => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::ARTICLE_PRIVACY_POLICY,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'User consent policy',
                self::ATTRIBUTE_NAME_KEY => t('User consent policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => $domainConfig->getId() === Domain::SECOND_DOMAIN_ID ? Article::PLACEMENT_FOOTER_2 : Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::USER_CONSENT_POLICY_ARTICLE,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Article for search testing',
                self::ATTRIBUTE_NAME_KEY => t('Article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Article text for search testing, the search phrase is &#34;Dina&#34;.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
            ],
        ];
    }

    /**
     * @param array $articles
     * @param int $domainId
     */
    private function createArticlesFromArray(array $articles, int $domainId): void
    {
        foreach ($articles as $article) {
            $this->createArticleFromArray($article, $domainId);
        }
    }

    /**
     * @param array $data
     * @param int $domainId
     */
    private function createArticleFromArray(array $data, int $domainId): void
    {
        $articleData = $this->articleDataFactory->create();
        $articleData->domainId = $domainId;
        $articleData->name = $data[self::ATTRIBUTE_NAME_KEY];
        $articleData->text = '<div class="gjs-text-ckeditor">' . $data[self::ATTRIBUTE_TEXT_KEY] . '</div>';
        $articleData->placement = $data[self::ATTRIBUTE_PLACEMENT_KEY];
        $articleData->seoH1 = $data[self::ATTRIBUTE_SEO_H1_KEY] ?? null;
        $articleData->seoTitle = $data[self::ATTRIBUTE_SEO_TITLE_KEY] ?? null;
        $articleData->seoMetaDescription = $data[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY] ?? null;
        $articleData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $data[self::ATTRIBUTE_PLAIN_NAME_KEY] . $domainId)->toString();

        $this->createArticleFromArticleData($articleData, $data[self::REFERENCE_NAME_KEY] ?? null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @param string|null $referenceName
     */
    private function createArticleFromArticleData(ArticleData $articleData, ?string $referenceName = null): void
    {
        $article = $this->articleFacade->create($articleData);

        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $article, $articleData->domainId);
        }
    }
}
