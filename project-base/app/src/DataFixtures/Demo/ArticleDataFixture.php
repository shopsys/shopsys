<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Article\Article;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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

    /**
     * @var string[]
     */
    private array $uuidPool = [
        '008cf1fb-218e-45c2-ae6e-02f9324948ba', '01088c42-38cc-43fd-a8a3-b7ee1de7bb8d', '02f09e4a-8dcf-434b-8952-cc7e53a48022',
        '02f82f30-bc84-468a-a0fe-e6f4a3961f81', '04cf6df1-ae17-45ff-8d40-efc61f7648e4', '0a131a21-9910-4a41-8e72-86bbe490a6fa',
        '0b9467f4-f918-43a8-a9df-cd13a0ff406c', '0bc2a2b5-0d24-4fea-b2b3-0a7074db331d', '0d00ae33-174d-4e63-baa8-4ed55a248452',
        '0d1adf66-27f1-4635-8bd3-69b4230c5d6d', '0f374c9b-b376-4785-ab0b-72faf9e9cd7d', '1533572c-2276-4f93-a08f-28e42dc36959',
        '1b645be0-0cc8-4e73-986d-0524fbe1d011', '1f691072-806d-4c66-9f46-ffb13ee58b52', '28067e08-7ef0-4a2f-bacf-2aa71a29e936',
        '29069a7e-1b93-4732-9e94-40c72717c2d1', '291dc068-1218-41de-bff5-25ee6df93959', '29435b8b-3dc3-44b9-a2ad-ca756ce165dc',
        '2ae421d8-55fc-411d-90a8-a116f3f1d8af', '2b7f834c-6bba-40c3-a7f0-334d329c2edc', '336d7002-70a5-4892-abda-4d1727578015',
        '34702528-39a7-485e-8d34-f1f259494915', '38875bb9-434e-413b-a7d8-8b0c38790c22', '3f7f14ff-088b-4b93-a98a-d54d11592fdf',
        '4c49cda0-f990-4570-8a8f-8179db8b9d3f', '4ce3f171-593b-4262-a953-06552497786f', '559bd6c4-afd0-49fc-a06a-cddf8224e321',
        '574e4b1a-14fd-411f-800d-353cce767f61', '57d3e6eb-2cf4-4ee2-925f-ace68698c608', '59e38e8e-fe23-49f9-aefc-490680bffce1',
        '5cb47014-4a93-4c6e-8c5f-794f7b0aff28', '5f041a12-5bc1-4c07-b4b7-30a147fe3c49', '623b13e2-3f8e-4882-bca9-3c95307a80eb',
        '62ae636c-e8a7-4d13-92ae-3fce4f66d1f7', '65594656-cf83-4530-a08e-a78f1a1d8358', '67f7cff2-98cd-49fd-a190-b25441a6f3d5',
        '6a4544c9-091a-466e-a5c8-06f767e06cb5', '6a8cb5e1-963a-44c1-902d-29f4d103b70a', '6e2ac47c-ebb6-4df7-97b2-3c75cf33eed4',
        '6f2a3bb3-1abb-45ed-8567-24a544dc0e69', '79f0125a-3afb-46d5-8c06-5054c4555265', '7a28b993-7009-4fba-997b-3710c1ead5d6',
        '7d082b5d-7f37-45ba-9378-4e38efe4e4d7', '7d592de9-e97b-4788-a48d-51b35b0ad1bf', '82f65e64-774d-47f5-acae-89688340d5b9',
        '83ad292b-8cb1-471a-b43b-c05d53d4a5d0', '84e9ecdc-4f1d-4592-a996-bcf1828fe5e2', '852cdc69-f9dc-4479-bf53-ce0fd9f6edfc',
        '8dc3dde0-d75c-44aa-8863-280f6e8b3427', '8fcddd4d-0123-4e79-acb2-19bfa312243b',
        '91631045-4c38-445a-b6ee-c582ea7a80a5', '93e7c7f4-d32e-4034-b490-f751a95e4d22', '98e09b58-d8a3-4788-af7c-1a59f8f7e26a',
        '9a346586-35e8-4d6a-8ef3-b54f4ff5df45', '9aafa22c-506f-4176-85c8-eab3c78dd2b0', '9c329515-8f19-416b-9951-bffefd5577df',
        'a3c2ae8c-9e10-4fa0-bf8f-4cf38ce8c47f', 'ab328b0e-7706-4911-b377-42468d3932f1', 'b17296d2-836c-4b5d-be9c-3c0e355716d4',
        'b5cf164a-f5f5-43fd-acc2-30c767887bcd', 'b75fa480-1f7b-44d2-bf36-5e50e7fcfe21', 'b8368ab7-3106-419b-8062-8c6feb74c3b4',
        'b9234224-1144-4185-b44f-f73a87348957', 'bb7e813e-51fa-4d60-928e-a9c379cb45c9', 'bd4a9edf-1db1-4a35-b706-63350a0023fe',
        'c3ea9c0e-d4fe-4bd0-9421-dd7248d7dfc1', 'c4210983-b756-41c8-9889-06e166db866c', 'c7d95e22-9af0-42db-8caa-4555b8fc002f',
        'c9885031-e62c-4ab6-a115-03e5e12827b7', 'cff98834-5a31-465d-8265-c8828292a2ba', 'de1a2e49-24cd-4f71-bb4b-7050fb4d05a3',
        'e2330a45-49dc-4f76-b297-feb498ee052b', 'e3a25fdb-b211-45d9-a40e-4be5c4a23544', 'e8398073-3046-4931-90b0-f41a22bf87a3',
        'e9fde24b-a67d-4302-9d32-720f4569893c', 'eb569f35-d258-42b0-b287-0da2d869e5d8', 'ef85bd91-4f33-41fd-9c69-4454fa57a329',
        'f4d20293-914a-4c1d-af10-78d16942fb7e', 'f972d2fa-e3c4-4401-bffc-3d2163438823', 'fb118ef9-4c72-4649-bda2-2e7a6d56a726',
        'fb50cbf6-ea11-4b58-88ac-f7a03dfb648f', 'fd76cb8e-35c0-496a-9209-5c3b80e1f331', 'fdbf42b0-ba8d-447e-9efa-220c786288df',
        'fea76318-e141-4a31-99da-ee476e82c5c2',
    ];

    /**
     * @param \App\Model\Article\ArticleFacade $articleFacade
     * @param \App\Model\Article\ArticleDataFactory $articleDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly ArticleFacade $articleFacade,
        private readonly ArticleDataFactoryInterface $articleDataFactory,
        private readonly Domain $domain,
    ) {
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
                    $locale,
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_TOP_MENU,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Shopping guide', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_TOP_MENU,
                self::ATTRIBUTE_SEO_H1_KEY => t(
                    'Shopping guide to improve your shopping experience',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                self::ATTRIBUTE_SEO_TITLE_KEY => t('Shopping guide for quick shopping', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY => t(
                    'Shopping guide - Tips and tricks how to quickly find what you are looking for',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
            ], [
                self::ATTRIBUTE_NAME_KEY => t('About us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Job at Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Cooperation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('For press', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Contacts', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Goods care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Installment plan', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Complaint', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Transport and payment', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Withdrawal from contract', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Where to find us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Department stores services', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
                self::REFERENCE_NAME_KEY => self::ARTICLE_TERMS_AND_CONDITIONS,
            ], [
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
                self::ATTRIBUTE_NAME_KEY => t('Information about cookies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    'Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::ARTICLE_COOKIES,
            ], [
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
        $articleData->uuid = array_pop($this->uuidPool);

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
