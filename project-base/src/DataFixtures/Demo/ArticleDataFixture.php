<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Article\ArticleDataFactory;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

class ArticleDataFixture extends AbstractReferenceFixture
{
    public const ARTICLE_TERMS_AND_CONDITIONS = 'article_terms_and_conditions';
    public const ARTICLE_PRIVACY_POLICY = 'article_privacy_policy';
    public const ARTICLE_COOKIES = 'article_cookies';

    private const ATTRIBUTE_NAME_KEY = 'name';
    private const ATTRIBUTE_TEXT_KEY = 'text';
    private const ATTRIBUTE_PLACEMENT_KEY = 'placement';
    private const ATTRIBUTE_SEO_H1_KEY = 'seoH1';
    private const ATTRIBUTE_SEO_TITLE_KEY = 'soeTitle';
    private const ATTRIBUTE_SEO_META_DESCRIPTION_KEY = 'soeMetaDescription';
    private const REFERENCE_NAME_KEY = 'referenceName';

    private ArticleDataFactory $articleDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \App\Model\Article\ArticleDataFactory $articleDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly ArticleFacade $articleFacade,
        ArticleDataFactoryInterface $articleDataFactory,
        private readonly Domain $domain
    ) {
        $this->articleDataFactory = $articleDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $data = $this->getDataForArticles($domainConfig->getLocale());
            $this->createArticlesFromArray($data, $domainConfig->getId());
        }

        if ($this->domain->isMultidomain()) {
            $this->changeDataForSecondDomain();
        }
    }

    /**
     * @param string $locale
     * @return string[][]
     */
    private function getDataForArticles(string $locale): array
    {
        return [
            [
                self::ATTRIBUTE_NAME_KEY => t('News', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_TOP_MENU,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Shopping guide', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_TOP_MENU,
                self::ATTRIBUTE_SEO_H1_KEY => t(
                    'Shopping guide to improve your shopping experience',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale
                ),
                self::ATTRIBUTE_SEO_TITLE_KEY => t('Shopping guide for quick shopping', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY => t(
                    'Shopping guide - Tips and tricks how to quickly find what you are looking for',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale
                ),
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER,
                self::REFERENCE_NAME_KEY => self::ARTICLE_TERMS_AND_CONDITIONS,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::ARTICLE_PRIVACY_POLICY,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Information about cookies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::ARTICLE_COOKIES,
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
        $articleData->text = $data[self::ATTRIBUTE_TEXT_KEY];
        $articleData->placement = $data[self::ATTRIBUTE_PLACEMENT_KEY];
        $articleData->seoH1 = $data[self::ATTRIBUTE_SEO_H1_KEY] ?? null;
        $articleData->seoTitle = $data[self::ATTRIBUTE_SEO_TITLE_KEY] ?? null;
        $articleData->seoMetaDescription = $data[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY] ?? null;

        $this->createArticleFromArticleData($articleData, $data[self::REFERENCE_NAME_KEY] ?? null);
    }

    /**
     * @param \App\Model\Article\ArticleData $articleData
     * @param string|null $referenceName
     */
    private function createArticleFromArticleData(ArticleData $articleData, ?string $referenceName = null): void
    {
        $article = $this->articleFacade->create($articleData);
        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $article, $articleData->domainId);
        }
    }

    private function changeDataForSecondDomain()
    {
        /** @var \App\Model\Article\Article $cookiesArticle */
        $cookiesArticle = $this->getReferenceForDomain(self::ARTICLE_COOKIES, Domain::SECOND_DOMAIN_ID);
        $cookiesArticleData = $this->articleDataFactory->createFromArticle($cookiesArticle);
        $cookiesArticleData->placement = Article::PLACEMENT_FOOTER;

        $this->articleFacade->edit($cookiesArticle->getId(), $cookiesArticleData);
    }
}
