<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\DataFixtures\Demo\DemoDataFactory\ContentDocumentBuilder;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Media\Media;

/**
 * The article shows products, so unlike the other articles it must be created after ProductDataFixture,
 * which has to be loaded after ArticleDataFixture because of the article settings.
 */
final class PressArticleDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string PLAIN_NAME = 'For press';

    public function __construct(
        private readonly ArticleFacade $articleFacade,
        private readonly ArticleDataFactory $articleDataFactory,
        private readonly ContentDocumentBuilder $contentDocumentBuilder,
        private readonly ContentDocumentFactory $contentDocumentFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $articleTitle = t('For press', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $articleData = $this->articleDataFactory->create($domainConfig->getId());
            $articleData->name = $articleTitle;
            $articleData->content = $this->contentDocumentFactory->createFromArray($this->createContent($locale));
            $articleData->placement = Article::PLACEMENT_FOOTER_1;
            $articleData->seo->h1 = $articleTitle;
            $articleData->seo->title = t('%articleTitle% | Demo shop', ['%articleTitle%' => $articleTitle], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $articleData->seo->metaDescription = t('Useful information from Demo shop: %articleTitle%.', ['%articleTitle%' => $articleTitle], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $articleData->uuid = Uuid::uuid5(ArticleDataFixture::UUID_NAMESPACE, self::PLAIN_NAME . $domainConfig->getId())->toString();

            $this->articleFacade->create($articleData);
        }
    }

    /**
     * @return array{version: int, profile: string, blocks: array<int, array<string, mixed>>}
     */
    private function createContent(string $locale): array
    {
        $mediaId = $this->getReference(MediaDataFixture::MEDIA_PREFIX . '1', Media::class)->getId();
        $secondMediaId = $this->getReference(MediaDataFixture::MEDIA_PREFIX . '2', Media::class)->getId();
        $builder = $this->contentDocumentBuilder->create();

        return $builder
            ->text('<p>' . t('Welcome to the Demo shop press centre. Here you can find company information, product selections, visual materials, and contacts for media enquiries.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')
            ->products(['9177759', '5964035'])
            ->text('<h2>' . t('Product inspiration', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h2><p>' . t('From a cosy film night to capturing a weekend away, the right technology makes everyday moments more enjoyable. Explore our selection of televisions and accessories for your next story.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')
            ->products(['9177759', '9176508', '5965879P', '532564'])
            ->text('<h2>' . t('About Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h2>')
            ->columns('1/3-2/3', [
                [$builder->create()->image($mediaId, t('Demo shop presentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale))],
                [$builder->create()->text('<p>' . t('A home is made of small everyday moments: a favourite song, a shared film, a photograph worth keeping. At Demo shop, we bring together electronics and accessories that help you enjoy them. Our team makes choosing easier with clear product information and friendly advice.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')],
            ])
            ->text('<h3>' . t('Key facts', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h3>')
            ->columns('1/3-1/3-1/3', [
                [$builder->create()->text('<p>' . t('3 language versions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')],
                [$builder->create()->text('<p>' . t('Electronics and home', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')],
                [$builder->create()->text('<p>' . t('Customer care every workday', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')],
            ])
            ->text('<h2>' . t('Let us help with your story', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h2>')
            ->columns('1/2-1/2', [
                [$builder->create()->text('<h3>' . t('Media contact', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h3><p>' . t('Planning an interview or looking for a comment on home electronics? Write to press@demo-shop.example with your topic and deadline. Our press team will help you find the right information.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')],
                [$builder->create()->text('<h3>' . t('Product materials', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h3><p>' . t('We can help you select products for a comparison, explain their features, and prepare supporting materials. Tell us who you are writing for so we can tailor the selection to your readers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')],
            ])
            ->text('<h3>' . t('What to include in your request', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h3>')
            ->columns('1/2-1/2', [
                [$builder->create()->text('<ul><li>' . t('Your publication and the topic of your story', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</li><li>' . t('Your audience and the products you are interested in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</li><li>' . t('Your deadline and preferred contact details', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</li></ul>')],
                [$builder->create()->text('<ul><li>' . t('The images and file formats you need', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</li><li>' . t('Any video footage or interview requirements', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</li><li>' . t('Possible dates for a store visit or a product demonstration', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</li></ul>')],
            ])
            ->text('<h2>' . t('Video materials', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h2><p>' . t('Looking for footage to accompany your story? Contact our press team to discuss product demonstrations and video materials for your publication.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')
            ->video('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->text('<h2>' . t('Stores and locations', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h2><p>' . t('Meet our team and see the products up close. Please arrange interviews, photography, and filming in advance with our press contact so we can prepare for your visit.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')
            ->map('https://maps.google.com/maps?&z=1&t=q&output=embed')
            ->text('<h2>' . t('Downloadable images', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h2><p>' . t('Bring your story to life with photographs of electronics in everyday settings. For publication-ready files and image credits, contact our press team and let us know which pictures you would like to use.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')
            ->image($secondMediaId, t('Downloadable Demo shop press image', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale))
            ->text('<h2>' . t('Frequently asked questions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</h2>')
            ->faq(t('How quickly do you answer press enquiries?', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '<p>' . t('We reply to press enquiries within one working day. For urgent requests, mention your deadline in the subject of the e-mail.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale) . '</p>')
            ->buttonLink(t('Explore electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '/')
            ->build();
    }

    #[Override]
    public function getDependencies(): array
    {
        return [
            ArticleDataFixture::class,
            ProductDataFixture::class,
            MediaDataFixture::class,
        ];
    }
}
