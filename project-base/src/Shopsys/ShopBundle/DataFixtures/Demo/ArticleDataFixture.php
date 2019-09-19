<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

class ArticleDataFixture extends AbstractReferenceFixture
{
    public const ARTICLE_TERMS_AND_CONDITIONS = 'article_terms_and_conditions';
    public const ARTICLE_PRIVACY_POLICY = 'article_privacy_policy';
    public const ARTICLE_COOKIES = 'article_cookies';

    protected const ATTRIBUTE_NAME_KEY = 'name';
    protected const ATTRIBUTE_TEXT_KEY = 'text';
    protected const ATTRIBUTE_PLACEMENT_KEY = 'placement';
    protected const REFERENCE_NAME_KEY = 'referenceName';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleFacade
     */
    protected $articleFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Article\ArticleDataFactory
     */
    protected $articleDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\ShopBundle\Model\Article\ArticleDataFactory $articleDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ArticleFacade $articleFacade,
        ArticleDataFactoryInterface $articleDataFactory,
        Domain $domain
    ) {
        $this->articleFacade = $articleFacade;
        $this->articleDataFactory = $articleDataFactory;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
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
    protected function getDataForArticles(string $locale): array
    {
        return [
            [
                self::ATTRIBUTE_NAME_KEY => t('News', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_TOP_MENU,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Shopping guide', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_TOP_MENU,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER,
                self::REFERENCE_NAME_KEY => self::ARTICLE_TERMS_AND_CONDITIONS,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Privacy policy', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::ARTICLE_PRIVACY_POLICY,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Information about cookies', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::ARTICLE_COOKIES,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Contact', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Donec at dolor mi. Nullam ornare, massa in cursus imperdiet, felis nisl auctor ante, vel aliquet tortor lacus sit amet ipsum. Proin ultrices euismod elementum. Integer sodales hendrerit tortor, vel semper turpis interdum eu. Phasellus quam tortor, feugiat vel condimentum vel, tristique et ipsum. Duis blandit lectus in odio cursus rutrum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam pulvinar massa at imperdiet venenatis. Maecenas convallis lobortis quam in fringilla. Mauris gravida turpis eget sapien imperdiet pulvinar. Nunc velit urna, fringilla nec est sit amet, accumsan varius nunc. Morbi sed tincidunt diam, sit amet laoreet nisl. Nulla tempus id lectus non lacinia.\n\nVestibulum interdum adipiscing iaculis. Nunc posuere pharetra velit. Nunc ac ante non massa scelerisque blandit sit amet vel velit. Integer in massa sed augue pulvinar malesuada. Pellentesque laoreet orci augue, in fermentum nisl feugiat ut. Nunc congue et nisi a interdum. Aenean mauris mi, interdum vel lacus et, placerat gravida augue. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed sagittis ipsum et consequat euismod. Praesent a ipsum dapibus, aliquet justo a, consectetur magna. Phasellus imperdiet tempor laoreet. Sed a accumsan lacus, accumsan faucibus dolor. Praesent euismod justo quis ipsum aliquam suscipit. Sed quis blandit urna.', [], 'dataFixtures', $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER,
            ],
        ];
    }

    /**
     * @param array $articles
     * @param int $domainId
     */
    protected function createArticlesFromArray(array $articles, int $domainId): void
    {
        foreach ($articles as $article) {
            $this->createArticleFromArray($article, $domainId);
        }
    }

    /**
     * @param array $data
     * @param int $domainId
     */
    protected function createArticleFromArray(array $data, int $domainId): void
    {
        $articleData = $this->articleDataFactory->create();
        $articleData->domainId = $domainId;
        $articleData->name = $data[self::ATTRIBUTE_NAME_KEY];
        $articleData->text = $data[self::ATTRIBUTE_TEXT_KEY];
        $articleData->placement = $data[self::ATTRIBUTE_PLACEMENT_KEY];

        $this->createArticleFromArticleData($articleData, $data[self::REFERENCE_NAME_KEY] ?? null);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Article\ArticleData $articleData
     * @param string|null $referenceName
     */
    protected function createArticleFromArticleData(ArticleData $articleData, ?string $referenceName = null): void
    {
        $article = $this->articleFacade->create($articleData);
        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $article, $articleData->domainId);
        }
    }

    protected function changeDataForSecondDomain()
    {
        /** @var \Shopsys\ShopBundle\Model\Article\Article $cookiesArticle */
        $cookiesArticle = $this->getReferenceForDomain(self::ARTICLE_COOKIES, 2);
        $cookiesArticleData = $this->articleDataFactory->createFromArticle($cookiesArticle);
        $cookiesArticleData->placement = Article::PLACEMENT_FOOTER;

        $this->articleFacade->edit($cookiesArticle->getId(), $cookiesArticleData);
    }
}
