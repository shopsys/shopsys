<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    private const string ATTRIBUTE_TEXT_KEY = 'text';
    private const string ATTRIBUTE_PLACEMENT_KEY = 'placement';
    private const string ATTRIBUTE_SEO_H1_KEY = 'seoH1';
    private const string ATTRIBUTE_SEO_TITLE_KEY = 'soeTitle';
    private const string ATTRIBUTE_SEO_META_DESCRIPTION_KEY = 'soeMetaDescription';
    private const string REFERENCE_NAME_KEY = 'referenceName';
    private const string UUID_NAMESPACE = '008cf1fb-218e-45c2-ae6e-02f9324948ba';

    public function __construct(
        private readonly ArticleFacade $articleFacade,
        private readonly ArticleDataFactory $articleDataFactory,
        private readonly DomainRouterFactory $domainRouterFactory,
        private readonly Domain $domain,
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
     * @return string[][]
     */
    private function getDataForArticles(DomainConfig $domainConfig): array
    {
        $locale = $domainConfig->getLocale();
        $homepageUrl = $this->generateUrlForHomepageOnDomain($domainConfig->getId());
        $categoryUrl = $this->domainsForDataFixtureProvider->isDomainIdAllowed($domainConfig->getId())
            ? $this->generateUrlForCategoryOnDomain(CategoryDataFixture::CATEGORY_ELECTRONICS, $domainConfig->getId())
            : '';
        $heading = t('What you should know', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $articles = [
            [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'About us',
                self::ATTRIBUTE_NAME_KEY => t('About us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Demo shop is a sample electronics retailer built to demonstrate a complete modern shopping experience.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('We focus on clear product information, straightforward ordering, and helpful customer care from selection to delivery.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Job at Shopsys',
                self::ATTRIBUTE_NAME_KEY => t('Job at Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('We welcome curious people who enjoy improving online shopping and solving practical customer problems.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Our demo team brings together technology, logistics, content, and customer care. Open roles would normally be listed here with responsibilities and contact details.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Cooperation',
                self::ATTRIBUTE_NAME_KEY => t('Cooperation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('We work with manufacturers, distributors, service partners, and creators who can bring useful products and knowledge to customers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('A good partnership starts with reliable data, clear commercial terms, and responsible customer support. Potential partners can contact our demo purchasing team.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'For press',
                self::ATTRIBUTE_NAME_KEY => t('For press', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createForPressArticleText($locale, $homepageUrl, $categoryUrl),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Goods care',
                self::ATTRIBUTE_NAME_KEY => t('Goods care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Regular care helps electronics remain safe, reliable, and pleasant to use for longer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Follow the manufacturer instructions, disconnect devices before cleaning, use suitable accessories, and avoid moisture, heat, and blocked ventilation.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Installment plan',
                self::ATTRIBUTE_NAME_KEY => t('Installment plan', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Selected purchases may be divided into regular payments through an external financing provider.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Before confirming financing, compare the total cost, repayment period, interest, fees, and eligibility conditions shown during checkout.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Complaint',
                self::ATTRIBUTE_NAME_KEY => t('Complaint', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('If a product develops a fault, prepare the order number, a clear description of the problem, and any useful photographs.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Customer care will explain the next steps, including delivery to a service centre, assessment, repair, replacement, or another available resolution.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Frequently Asked Questions FAQ',
                self::ATTRIBUTE_NAME_KEY => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Here you can find quick answers about orders, availability, delivery, payment, returns, complaints, and customer accounts.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('If the answer is not listed, contact customer care with your order number so the team can help without unnecessary delay.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Transport and payment',
                self::ATTRIBUTE_NAME_KEY => t('Transport and payment', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Available delivery and payment methods depend on the destination, order size, stock location, and selected products.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('The checkout always shows the current price and estimated delivery time before the order is confirmed.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Withdrawal from contract',
                self::ATTRIBUTE_NAME_KEY => t('Withdrawal from contract', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Consumers may return eligible online purchases within the period shown in the applicable terms and conditions.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Return the product complete, safely packed, and with its accessories. This demonstration text is not a substitute for the legally required instructions of a real merchant.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Terms and conditions of eshop',
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('These demonstration terms describe the usual flow of ordering, payment, delivery, complaints, and returns in a sample online store.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('They are illustrative demo content only and must not be used as legal terms for a real business without professional review and company-specific information.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Where to find us',
                self::ATTRIBUTE_NAME_KEY => t('Where to find us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Demo stores provide personal collection, product advice, and selected customer-care services.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Check the store detail before visiting for its current address, opening hours, available services, and holiday schedule.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Department stores services',
                self::ATTRIBUTE_NAME_KEY => t('Department stores services', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Our sample stores combine personal collection with practical assistance before and after purchase.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Typical services include order pickup, basic product advice, complaint intake, returns, and information about delivery options.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Terms and conditions of department stores',
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('These demonstration store terms summarise reservations, personal collection, payment, returns, and complaint handling at sample branches.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('They are intended only for product demonstration and require legal and operational adaptation before use by a real retailer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
                self::REFERENCE_NAME_KEY => self::ARTICLE_TERMS_AND_CONDITIONS,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Privacy policy',
                self::ATTRIBUTE_NAME_KEY => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('This demonstration privacy policy explains how a sample online store may process account, order, communication, and technical data.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('A real policy must identify the controller, purposes, legal bases, retention periods, recipients, security measures, and all rights available to customers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::ARTICLE_PRIVACY_POLICY,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'User consent policy',
                self::ATTRIBUTE_NAME_KEY => t('User consent policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Optional consent can be used for clearly described purposes such as personalised marketing or selected analytics.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('Consent must be voluntary, specific, informed, and easy to withdraw. Refusing optional consent must not prevent completion of an ordinary purchase.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => $domainConfig->getId() === Domain::SECOND_DOMAIN_ID ? Article::PLACEMENT_FOOTER_2 : Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::USER_CONSENT_POLICY_ARTICLE,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'How we work with reviews',
                self::ATTRIBUTE_NAME_KEY => t('How we work with reviews', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
                    t('Customer reviews help shoppers make informed decisions and share their experience with purchased products.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    $heading,
                    t('We publish reviews after checking that they comply with our review guidelines. Reviews from customers whose purchase we can confirm are marked as verified purchases.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::PRODUCT_REVIEW_POLICY_ARTICLE,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Article for search testing',
                self::ATTRIBUTE_NAME_KEY => t('How Dina chooses reliable electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => $this->createArticleText(
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

    private function createArticleText(string $intro, string $heading, string $detail): string
    {
        return sprintf('<div class="gjs-text-ckeditor"><p>%s</p><h2>%s</h2><p>%s</p></div>', $intro, $heading, $detail);
    }

    private function createForPressArticleText(string $locale, string $homepageUrl, string $categoryUrl): string
    {
        $intro = t('Welcome to the Demo shop press centre. Here you can find company information, product selections, visual materials, and contacts for media enquiries.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mediaContact = t('Media contact', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mediaContactText = t('Planning an interview or looking for a comment on home electronics? Write to press@demo-shop.example with your topic and deadline. Our press team will help you find the right information.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $assets = t('Product materials', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $assetsText = t('We can help you select products for a comparison, explain their features, and prepare supporting materials. Tell us who you are writing for so we can tailor the selection to your readers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $markets = t('3 language versions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $categories = t('Electronics and home', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $support = t('Customer care every workday', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $aboutHeading = t('About Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $aboutText = t('A home is made of small everyday moments: a favourite song, a shared film, a photograph worth keeping. At Demo shop, we bring together electronics and accessories that help you enjoy them. Our team makes choosing easier with clear product information and friendly advice.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $aboutImageAlt = t('Demo shop presentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $factsHeading = t('Key facts', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $videoHeading = t('Video materials', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $storesHeading = t('Stores and locations', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $imagesHeading = t('Downloadable images', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $downloadableImageAlt = t('Downloadable Demo shop press image', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $inspirationHeading = t('Product inspiration', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $inspirationText = t('From a cosy film night to capturing a weekend away, the right technology makes everyday moments more enjoyable. Explore our selection of televisions and accessories for your next story.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $pressHeading = t('Let us help with your story', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $checklistHeading = t('What to include in your request', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $requestTopic = t('Your publication and the topic of your story', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $requestAudience = t('Your audience and the products you are interested in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $requestDeadline = t('Your deadline and preferred contact details', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $requestImages = t('The images and file formats you need', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $requestVideo = t('Any video footage or interview requirements', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $requestVisit = t('Possible dates for a store visit or a product demonstration', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $videoText = t('Looking for footage to accompany your story? Contact our press team to discuss product demonstrations and video materials for your publication.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $storesText = t('Meet our team and see the products up close. Please arrange interviews, photography, and filming in advance with our press contact so we can prepare for your visit.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $imagesText = t('Bring your story to life with photographs of electronics in everyday settings. For publication-ready files and image credits, contact our press team and let us know which pictures you would like to use.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $moreProducts = t('Explore electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        return str_replace(['    ', PHP_EOL], '', trim(<<<EOT
            <div class="gjs-text-ckeditor"><p>{$intro}</p></div>
            <div class="gjs-products" data-products="9177759,5964035">
                <div data-product="9177759" class="gjs-product"></div>
                <div data-product="5964035" class="gjs-product"></div>
            </div>
            <div class="gjs-text-ckeditor"><h2>{$inspirationHeading}</h2><p>{$inspirationText}</p></div>
            <div class="gjs-products" data-products="9177759,9176508,5965879P,532564">
                <div data-product="9177759" class="gjs-product"></div>
                <div data-product="9176508" class="gjs-product"></div>
                <div data-product="5965879P" class="gjs-product"></div>
                <div data-product="532564" class="gjs-product"></div>
            </div>
            <div class="gjs-text-ckeditor"><h2>{$aboutHeading}</h2></div>
            <div class="gjs-text-with-image gjs-text-with-image-float-left">
                <img src="{$homepageUrl}content/images/blogArticle/600.jpg" class="image" alt="{$aboutImageAlt}" />
                <div class="gjs-text-ckeditor text"><p>{$aboutText}</p></div>
            </div>
            <div class="gjs-text-ckeditor"><h3>{$factsHeading}</h3></div>
            <div class="row">
                <div class="column"><div class="gjs-text-ckeditor">{$markets}</div></div>
                <div class="column"><div class="gjs-text-ckeditor">{$categories}</div></div>
                <div class="column"><div class="gjs-text-ckeditor">{$support}</div></div>
            </div>
            <div class="gjs-text-ckeditor"><h2>{$pressHeading}</h2></div>
            <div class="row">
                <div class="column"><div class="gjs-text-ckeditor"><h3>{$mediaContact}</h3><p>{$mediaContactText}</p></div></div>
                <div class="column"><div class="gjs-text-ckeditor"><h3>{$assets}</h3><p>{$assetsText}</p></div></div>
            </div>
            <div class="gjs-text-ckeditor"><h3>{$checklistHeading}</h3></div>
            <div class="row" role="list">
                <div class="column" role="presentation">
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$requestTopic}</div></div>
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$requestAudience}</div></div>
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$requestDeadline}</div></div>
                </div>
                <div class="column" role="presentation">
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$requestImages}</div></div>
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$requestVideo}</div></div>
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$requestVisit}</div></div>
                </div>
            </div>
            <div class="gjs-text-ckeditor"><h2>{$videoHeading}</h2><p>{$videoText}</p></div>
            <video poster="{$homepageUrl}content/images/blogArticle/600.jpg" controls preload="none"></video>
            <div class="gjs-text-ckeditor"><h2>{$storesHeading}</h2><p>{$storesText}</p></div>
            <iframe src="https://maps.google.com/maps?&z=1&t=q&output=embed" style="height: 350px; width: 100%; border: 0"></iframe>
            <div class="gjs-text-ckeditor"><h2>{$imagesHeading}</h2><p>{$imagesText}</p></div>
            <img src="{$homepageUrl}content/images/blogArticle/601.jpg" class="image-position-left" alt="{$downloadableImageAlt}" />
            <a class="gjs-button-link button-link-position-left" title="{$moreProducts}" href="{$categoryUrl}"><div class="gjs-text-ckeditor text">{$moreProducts}</div></a>
        EOT));
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
        $articleData->text = $data[self::ATTRIBUTE_TEXT_KEY];
        $articleData->placement = $data[self::ATTRIBUTE_PLACEMENT_KEY];
        $articleData->seoH1 = $data[self::ATTRIBUTE_SEO_H1_KEY] ?? null;
        $articleData->seoTitle = $data[self::ATTRIBUTE_SEO_TITLE_KEY] ?? null;
        $articleData->seoMetaDescription = $data[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY] ?? null;
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

    private function generateUrlForHomepageOnDomain(int $domainId): string
    {
        $router = $this->domainRouterFactory->getRouter($domainId);

        return $router->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function generateUrlForCategoryOnDomain(string $categoryReferenceName, int $domainId): string
    {
        $router = $this->domainRouterFactory->getRouter($domainId);
        $categoryReference = $this->getReference($categoryReferenceName, Category::class);

        return $router->generate(
            'front_product_list',
            ['id' => $categoryReference->getId()],
            UrlGeneratorInterface::RELATIVE_PATH,
        );
    }

    private function isMandatoryArticle(array $articleInputData): bool
    {
        return array_key_exists(self::REFERENCE_NAME_KEY, $articleInputData) && in_array($articleInputData[self::REFERENCE_NAME_KEY], self::ARTICLES_MANDATORY_ON_ALL_DOMAINS, true);
    }
}
