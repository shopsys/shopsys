<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\DataFixtures\Demo\DemoDataFactory\ContentDocumentBuilder;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

class ArticleDataFixture extends AbstractReferenceFixture
{
    public const string ARTICLE_TERMS_AND_CONDITIONS = 'article_terms_and_conditions';
    public const string ARTICLE_PRIVACY_POLICY = 'article_privacy_policy';
    public const string USER_CONSENT_POLICY_ARTICLE = 'article_user_consent_policy';
    public const string PRODUCT_REVIEW_POLICY_ARTICLE = 'article_product_review_policy';

    private const array ARTICLES_MANDATORY_ON_ALL_DOMAINS = [
        self::ARTICLE_TERMS_AND_CONDITIONS,
        self::ARTICLE_PRIVACY_POLICY,
        self::USER_CONSENT_POLICY_ARTICLE,
        self::PRODUCT_REVIEW_POLICY_ARTICLE,
    ];

    private const string ATTRIBUTE_NAME_KEY = 'name';
    private const string ATTRIBUTE_PLAIN_NAME_KEY = 'plainName';
    private const string ATTRIBUTE_CONTENT_KEY = 'content';
    private const string ATTRIBUTE_PLACEMENT_KEY = 'placement';
    private const string ATTRIBUTE_SEO_H1_KEY = 'seoH1';
    private const string ATTRIBUTE_SEO_TITLE_KEY = 'soeTitle';
    private const string ATTRIBUTE_SEO_META_DESCRIPTION_KEY = 'soeMetaDescription';
    private const string REFERENCE_NAME_KEY = 'referenceName';
    public const string UUID_NAMESPACE = '008cf1fb-218e-45c2-ae6e-02f9324948ba';

    public function __construct(
        private readonly ArticleFacade $articleFacade,
        private readonly ArticleDataFactory $articleDataFactory,
        private readonly Domain $domain,
        private readonly ContentDocumentBuilder $contentDocumentBuilder,
        private readonly ContentDocumentFactory $contentDocumentFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $data = $this->getDataForArticles($domainConfig);
            $this->createArticlesFromArray($data, $domainConfig->getId());
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDataForArticles(DomainConfig $domainConfig): array
    {
        $locale = $domainConfig->getLocale();
        $heading = t('What you should know', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $articles = [
            [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'About us',
                self::ATTRIBUTE_NAME_KEY => t('About us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Demo shop is a sample electronics retailer built to demonstrate a complete modern shopping experience.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('We focus on clear product information, straightforward ordering, and helpful customer care from selection to delivery.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Job at Shopsys',
                self::ATTRIBUTE_NAME_KEY => t('Job at Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('We welcome curious people who enjoy improving online shopping and solving practical customer problems.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Our demo team brings together technology, logistics, content, and customer care. Open roles would normally be listed here with responsibilities and contact details.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Cooperation',
                self::ATTRIBUTE_NAME_KEY => t('Cooperation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('We work with manufacturers, distributors, service partners, and creators who can bring useful products and knowledge to customers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('A good partnership starts with reliable data, clear commercial terms, and responsible customer support. Potential partners can contact our demo purchasing team.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Goods care',
                self::ATTRIBUTE_NAME_KEY => t('Goods care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Regular care helps electronics remain safe, reliable, and pleasant to use for longer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Follow the manufacturer instructions, disconnect devices before cleaning, use suitable accessories, and avoid moisture, heat, and blocked ventilation.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Installment plan',
                self::ATTRIBUTE_NAME_KEY => t('Installment plan', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Selected purchases may be divided into regular payments through an external financing provider.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Before confirming financing, compare the total cost, repayment period, interest, fees, and eligibility conditions shown during checkout.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Complaint',
                self::ATTRIBUTE_NAME_KEY => t('Complaint', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('If a product develops a fault, prepare the order number, a clear description of the problem, and any useful photographs.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Customer care will explain the next steps, including delivery to a service centre, assessment, repair, replacement, or another available resolution.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Frequently Asked Questions FAQ',
                self::ATTRIBUTE_NAME_KEY => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Here you can find quick answers about orders, availability, delivery, payment, returns, complaints, and customer accounts.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('If the answer is not listed, contact customer care with your order number so the team can help without unnecessary delay.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Transport and payment',
                self::ATTRIBUTE_NAME_KEY => t('Transport and payment', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Available delivery and payment methods depend on the destination, order size, stock location, and selected products.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('The checkout always shows the current price and estimated delivery time before the order is confirmed.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Withdrawal from contract',
                self::ATTRIBUTE_NAME_KEY => t('Withdrawal from contract', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Consumers may return eligible online purchases within the period shown in the applicable terms and conditions.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Return the product complete, safely packed, and with its accessories. This demonstration text is not a substitute for the legally required instructions of a real merchant.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Terms and conditions of eshop',
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('These demonstration terms describe the usual flow of ordering, payment, delivery, complaints, and returns in a sample online store.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('They are illustrative demo content only and must not be used as legal terms for a real business without professional review and company-specific information.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Where to find us',
                self::ATTRIBUTE_NAME_KEY => t('Where to find us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Demo stores provide personal collection, product advice, and selected customer-care services.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Check the store detail before visiting for its current address, opening hours, available services, and holiday schedule.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Department stores services',
                self::ATTRIBUTE_NAME_KEY => t('Department stores services', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Our sample stores combine personal collection with practical assistance before and after purchase.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Typical services include order pickup, basic product advice, complaint intake, returns, and information about delivery options.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Terms and conditions of department stores',
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('These demonstration store terms summarise reservations, personal collection, payment, returns, and complaint handling at sample branches.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('They are intended only for product demonstration and require legal and operational adaptation before use by a real retailer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
                self::REFERENCE_NAME_KEY => self::ARTICLE_TERMS_AND_CONDITIONS,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Privacy policy',
                self::ATTRIBUTE_NAME_KEY => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('This demonstration privacy policy explains how a sample online store may process account, order, communication, and technical data.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('A real policy must identify the controller, purposes, legal bases, retention periods, recipients, security measures, and all rights available to customers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::ARTICLE_PRIVACY_POLICY,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'User consent policy',
                self::ATTRIBUTE_NAME_KEY => t('User consent policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Optional consent can be used for clearly described purposes such as personalised marketing or selected analytics.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Consent must be voluntary, specific, informed, and easy to withdraw. Refusing optional consent must not prevent completion of an ordinary purchase.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => $domainConfig->getId() === Domain::SECOND_DOMAIN_ID ? Article::PLACEMENT_FOOTER_2 : Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::USER_CONSENT_POLICY_ARTICLE,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'How we work with reviews',
                self::ATTRIBUTE_NAME_KEY => t('How we work with reviews', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Customer reviews help shoppers make informed decisions and share their experience with purchased products.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('We publish reviews after checking that they comply with our review guidelines. Reviews from customers whose purchase we can confirm are marked as verified purchases.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::PRODUCT_REVIEW_POLICY_ARTICLE,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Article for search testing',
                self::ATTRIBUTE_NAME_KEY => t('How Dina chooses reliable electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_CONTENT_KEY => $this->createArticleContent(
                    t('Dina is preparing a comfortable home office and compares a monitor, headphones, and practical accessories before ordering.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    t('A simple checklist before purchase', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    t('She checks dimensions, compatibility, warranty conditions, and delivery options so every product fits the way she works.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
            ],
        ];

        foreach ($articles as &$article) {
            $articleTitle = $article[self::ATTRIBUTE_NAME_KEY];
            $article[self::ATTRIBUTE_SEO_H1_KEY] = $articleTitle;
            $article[self::ATTRIBUTE_SEO_TITLE_KEY] = t('%articleTitle% | Demo shop', ['%articleTitle%' => $articleTitle], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $article[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY] = t('Useful information from Demo shop: %articleTitle%.', ['%articleTitle%' => $articleTitle], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        unset($article);

        return $articles;
    }

    /**
     * @return array{version: int, profile: string, blocks: array<int, array<string, mixed>>}
     */
    private function createArticleContent(string $intro, string $heading, string $detail): array
    {
        return $this->contentDocumentBuilder->create()
            ->text(sprintf('<p>%s</p><h2>%s</h2><p>%s</p>', $intro, $heading, $detail))
            ->build();
    }

    private function createArticlesFromArray(array $articles, int $domainId): void
    {
        foreach ($articles as $article) {
            if (!$this->domainsForDataFixtureProvider->isDomainIdAllowed($domainId) && !$this->isMandatoryArticle($article)) {
                continue;
            }

            $this->createArticleFromArray($article, $domainId);
        }
    }

    private function createArticleFromArray(array $data, int $domainId): void
    {
        $articleData = $this->articleDataFactory->create($domainId);
        $articleData->name = $data[self::ATTRIBUTE_NAME_KEY];
        $articleData->content = $this->contentDocumentFactory->createFromArray($data[self::ATTRIBUTE_CONTENT_KEY]);
        $articleData->placement = $data[self::ATTRIBUTE_PLACEMENT_KEY];
        $articleData->seo->h1 = $data[self::ATTRIBUTE_SEO_H1_KEY] ?? null;
        $articleData->seo->title = $data[self::ATTRIBUTE_SEO_TITLE_KEY] ?? null;
        $articleData->seo->metaDescription = $data[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY] ?? null;
        $articleData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $data[self::ATTRIBUTE_PLAIN_NAME_KEY] . $domainId)->toString();

        $this->createArticleFromArticleData($articleData, $data[self::REFERENCE_NAME_KEY] ?? null);
    }

    private function createArticleFromArticleData(ArticleData $articleData, ?string $referenceName = null): void
    {
        $article = $this->articleFacade->create($articleData);

        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $article, $articleData->domainId);
        }
    }

    private function isMandatoryArticle(array $articleInputData): bool
    {
        return array_key_exists(self::REFERENCE_NAME_KEY, $articleInputData) && in_array($articleInputData[self::REFERENCE_NAME_KEY], self::ARTICLES_MANDATORY_ON_ALL_DOMAINS, true);
    }
}
