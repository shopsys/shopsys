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

    private const array ARTICLES_MANDATORY_ON_ALL_DOMAINS = [
        self::ARTICLE_TERMS_AND_CONDITIONS,
        self::ARTICLE_PRIVACY_POLICY,
        self::USER_CONSENT_POLICY_ARTICLE,
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

        $placeholderText = t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        $articles = [
            [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'About us',
                self::ATTRIBUTE_NAME_KEY => t('About us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Job at Shopsys',
                self::ATTRIBUTE_NAME_KEY => t('Job at Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Cooperation',
                self::ATTRIBUTE_NAME_KEY => t('Cooperation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'For press',
                self::ATTRIBUTE_NAME_KEY => t('For press', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ) . '
                    <div class="gjs-products" data-products="9177759,5964035">
                        <div data-product="9177759" data-product-name="22&quot; Sencor SLE 22F46DM4 HELLO KITTY" class="gjs-product"></div>
                        <div data-product="5964035" data-product-name="Invisible product" class="gjs-product"></div>
                    </div>
                    <div class="gjs-products" data-products="9177759,9176508,5965879P,532564,1532564,5960453">
                        <div data-product="9177759" data-product-name="22\" Sencor SLE 22F46DM4 HELLO KITTY" class="gjs-product"></div>
                        <div data-product="9176508" data-product-name="32\" Philips 32PFL4308" class="gjs-product"></div>
                        <div data-product="5965879P" data-product-name="47\" LG 47LA790V (FHD)" class="gjs-product"></div>
                        <div data-product="532564" data-product-name="Canon EOS 700D" class="gjs-product"></div>
                        <div data-product="1532564" data-product-name="Canon EH-22M" class="gjs-product"></div>
                        <div data-product="5960453" data-product-name="A4tech mouse X-710BK, OSCAR Game, 2000DPI, black," class="gjs-product"></div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor" data-gjs-type="text" id="ic192p" style="text-align: center">
                                Eius distinctio numquam doloremque quas debitis. Nam unde, et quos nesciunt mollitia nostrum molestiae
                                incidunt offic.
                            </div>
                        </div>
                        <div class="column">
                            <div class="gjs-text-ckeditor" data-gjs-type="text" id="itv9uf" style="text-align: center">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Libero, esse! Eius distinctio numquam doloremque
                                quas debitis. Nam unde, et quos nesciunt mollitia nostrum molestiae incidunt officiis dolorum similique ab
                                nihil?
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="iv009r" class="column" style="text-align: center">
                            <div class="gjs-text-ckeditor">Integer id pretium quam, id placerat nulla.</div>
                        </div>
                        <div id="idyvqa" class="column" style="text-align: center">
                            <div class="gjs-text-ckeditor">Nam auctor neque quis tincidunt tempus</div>
                        </div>
                        <div id="i0updc" class="column" style="text-align: center">
                            <div class="gjs-text-ckeditor">Praesent tristique lorem mi, eget varius quam aliquam eget.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor">
                                <h2 id="i75nl3" draggable="true">H2 Integer id pretium quam, id placerat nulla.</h2>
                            </div>
                            <div
                                data-image-position="left"
                                class="gjs-text-with-image gjs-text-with-image-float-left"
                            >
                                <img
                                    data-image-position="left"
                                    src="' . $homepageUrl . 'content/images/blogArticle/default/600.jpg"
                                    class="image"
                                />
                                <div class="gjs-text-ckeditor text" data-gjs-type="text">
                                    Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed
                                    placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a
                                    arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor
                                    neque quis tincidunt tempus
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor" data-gjs-type="text">
                                <h3 id="i41lfa" draggable="true" style="margin: 0px 0px 0px 0px">H3 Id placerat nulla.</h3>
                            </div>
                            <div
                                class="gjs-table-custom"
                                rows="2"
                                columns="2"
                                variant="default"
                                id="ifvoxk"
                                style="margin: 20px 0px 20px 0px"
                            >
                                <table class="default">
                                    <tbody>
                                        <tr>
                                            <td><div class="gjs-text-ckeditor text" data-gjs-type="text">1st row 1st column</div></td>
                                            <td><div class="gjs-text-ckeditor text" data-gjs-type="text">1st row 2th column</div></td>
                                        </tr>
                                        <tr>
                                            <td><div class="gjs-text-ckeditor text" data-gjs-type="text">2nd row 1st column</div></td>
                                            <td><div class="gjs-text-ckeditor text" data-gjs-type="text">2nd row 2nd columns</div></td>
                                        </tr>
                                        <tr>
                                            <td><div class="gjs-text-ckeditor text" data-gjs-type="text">3rd row 1st column</div></td>
                                            <td><div class="gjs-text-ckeditor text" data-gjs-type="text">3nd row 2nd columns</div></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor" data-gjs-type="text">
                                <h4 id="ioawsm" draggable="true">H4 Vidson</h4>
                            </div>
                            <video
                                src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3R5bGU9ImZpbGw6IHJnYmEoMCwwLDAsMC4xNSk7IHRyYW5zZm9ybTogc2NhbGUoMC43NSkiPgogICAgICAgIDxwYXRoIGQ9Ik04LjUgMTMuNWwyLjUgMyAzLjUtNC41IDQuNSA2SDVtMTYgMVY1YTIgMiAwIDAgMC0yLTJINWMtMS4xIDAtMiAuOS0yIDJ2MTRjMCAxLjEuOSAyIDIgMmgxNGMxLjEgMCAyLS45IDItMnoiPjwvcGF0aD4KICAgICAgPC9zdmc+"
                                controls
                                id="ijwm64"
                                style="margin: 0px 0px 0px 0px"
                            ></video>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor" data-gjs-type="text">
                                <h5 id="i4utlb" draggable="true">H5 Mapson</h5>
                            </div>
                            <iframe
                                id="in1zyi"
                                src="https://maps.google.com/maps?&z=1&t=q&output=embed"
                                style="height: 350px; width: 100%; border: 0"
                            ></iframe>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor" data-gjs-type="text">
                                <h6 id="i1dz5h" draggable="true">H6 Imegson</h6>
                            </div>
                            <img
                                data-image-position="left"
                                src="' . $homepageUrl . 'content/images/blogArticle/default/601.jpg"
                                class="image-position-left"
                            />
                        </div>
                    </div>
                    <a
                        data-link-position="center"
                        data-backgroundcolor="#00C8B7"
                        class="gjs-button-link button-link-position-center"
                        title="More products"
                        href="' . $categoryUrl . '"
                    >
                        <div class="gjs-text-ckeditor text" data-gjs-type="text">More products</div>
                    </a>
            ',
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_1,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Goods care',
                self::ATTRIBUTE_NAME_KEY => t('Goods care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Installment plan',
                self::ATTRIBUTE_NAME_KEY => t('Installment plan', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Complaint',
                self::ATTRIBUTE_NAME_KEY => t('Complaint', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_2,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Frequently Asked Questions FAQ',
                self::ATTRIBUTE_NAME_KEY => t('Frequently Asked Questions FAQ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Transport and payment',
                self::ATTRIBUTE_NAME_KEY => t('Transport and payment', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Withdrawal from contract',
                self::ATTRIBUTE_NAME_KEY => t('Withdrawal from contract', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Terms and conditions of eshop',
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_3,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Where to find us',
                self::ATTRIBUTE_NAME_KEY => t('Where to find us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Department stores services',
                self::ATTRIBUTE_NAME_KEY => t('Department stores services', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Terms and conditions of department stores',
                self::ATTRIBUTE_NAME_KEY => t('Terms and conditions of department stores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_FOOTER_4,
                self::REFERENCE_NAME_KEY => self::ARTICLE_TERMS_AND_CONDITIONS,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Privacy policy',
                self::ATTRIBUTE_NAME_KEY => t('Privacy policy', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t(
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
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
                    '<p>Morbi posuere mauris dolor, quis accumsan dolor ullamcorper eget. Phasellus at elementum magna, et pretium neque. Praesent tristique lorem mi, eget varius quam aliquam eget. Vivamus ultrices interdum nisi, sed placerat lectus fermentum non. Phasellus ac quam vitae nisi aliquam vestibulum. Sed rhoncus tortor a arcu sagittis placerat. Nulla lectus nunc, ultrices ac faucibus sed, accumsan nec diam. Nam auctor neque quis tincidunt tempus. Nunc eget risus tristique, lobortis metus vitae, pellentesque leo. Vivamus placerat turpis ac dolor vehicula tincidunt. Sed venenatis, ante id ultrices convallis, lacus elit porttitor dolor, non porta risus ipsum ac justo. Integer id pretium quam, id placerat nulla.</p>',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                self::ATTRIBUTE_PLACEMENT_KEY => $domainConfig->getId() === Domain::SECOND_DOMAIN_ID ? Article::PLACEMENT_FOOTER_2 : Article::PLACEMENT_NONE,
                self::REFERENCE_NAME_KEY => self::USER_CONSENT_POLICY_ARTICLE,
            ], [
                self::ATTRIBUTE_PLAIN_NAME_KEY => 'Article for search testing',
                self::ATTRIBUTE_NAME_KEY => t('Article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_TEXT_KEY => t('<p>Article text for search testing, the search phrase is &#34;Dina&#34;.</p>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                self::ATTRIBUTE_PLACEMENT_KEY => Article::PLACEMENT_NONE,
            ],
        ];

        foreach ($articles as &$article) {
            if ($article[self::ATTRIBUTE_PLAIN_NAME_KEY] === 'For press') {
                $article[self::ATTRIBUTE_TEXT_KEY] = $this->createForPressArticleText($locale, $homepageUrl, $categoryUrl);
            } elseif ($article[self::ATTRIBUTE_PLAIN_NAME_KEY] === 'Article for search testing') {
                $article[self::ATTRIBUTE_NAME_KEY] = t('How Dina chooses reliable electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $article[self::ATTRIBUTE_TEXT_KEY] = $this->createSearchArticleText($locale);
            } elseif ($article[self::ATTRIBUTE_TEXT_KEY] === $placeholderText) {
                $article[self::ATTRIBUTE_TEXT_KEY] = $this->createStandardArticleText($article[self::ATTRIBUTE_PLAIN_NAME_KEY], $locale);
            }

            $articleTitle = $article[self::ATTRIBUTE_NAME_KEY];
            $article[self::ATTRIBUTE_SEO_H1_KEY] = $articleTitle;
            $article[self::ATTRIBUTE_SEO_TITLE_KEY] = t('%articleTitle% | Demo shop', ['%articleTitle%' => $articleTitle], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $article[self::ATTRIBUTE_SEO_META_DESCRIPTION_KEY] = t('Useful information from Demo shop: %articleTitle%.', ['%articleTitle%' => $articleTitle], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        unset($article);

        return $articles;
    }

    private function createStandardArticleText(string $articleName, string $locale): string
    {
        $contentTranslationKeysByArticleName = [
            'About us' => [
                'intro' => t('Demo shop is a sample electronics retailer built to demonstrate a complete modern shopping experience.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('We focus on clear product information, straightforward ordering, and helpful customer care from selection to delivery.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Job at Shopsys' => [
                'intro' => t('We welcome curious people who enjoy improving online shopping and solving practical customer problems.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('Our demo team brings together technology, logistics, content, and customer care. Open roles would normally be listed here with responsibilities and contact details.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Cooperation' => [
                'intro' => t('We work with manufacturers, distributors, service partners, and creators who can bring useful products and knowledge to customers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('A good partnership starts with reliable data, clear commercial terms, and responsible customer support. Potential partners can contact our demo purchasing team.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Goods care' => [
                'intro' => t('Regular care helps electronics remain safe, reliable, and pleasant to use for longer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('Follow the manufacturer instructions, disconnect devices before cleaning, use suitable accessories, and avoid moisture, heat, and blocked ventilation.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Installment plan' => [
                'intro' => t('Selected purchases may be divided into regular payments through an external financing provider.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('Before confirming financing, compare the total cost, repayment period, interest, fees, and eligibility conditions shown during checkout.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Complaint' => [
                'intro' => t('If a product develops a fault, prepare the order number, a clear description of the problem, and any useful photographs.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('Customer care will explain the next steps, including delivery to a service centre, assessment, repair, replacement, or another available resolution.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Frequently Asked Questions FAQ' => [
                'intro' => t('Here you can find quick answers about orders, availability, delivery, payment, returns, complaints, and customer accounts.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('If the answer is not listed, contact customer care with your order number so the team can help without unnecessary delay.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Transport and payment' => [
                'intro' => t('Available delivery and payment methods depend on the destination, order size, stock location, and selected products.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('The checkout always shows the current price and estimated delivery time before the order is confirmed.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Withdrawal from contract' => [
                'intro' => t('Consumers may return eligible online purchases within the period shown in the applicable terms and conditions.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('Return the product complete, safely packed, and with its accessories. This demonstration text is not a substitute for the legally required instructions of a real merchant.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Terms and conditions of eshop' => [
                'intro' => t('These demonstration terms describe the usual flow of ordering, payment, delivery, complaints, and returns in a sample online store.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('They are illustrative demo content only and must not be used as legal terms for a real business without professional review and company-specific information.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Where to find us' => [
                'intro' => t('Demo stores provide personal collection, product advice, and selected customer-care services.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('Check the store detail before visiting for its current address, opening hours, available services, and holiday schedule.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Department stores services' => [
                'intro' => t('Our sample stores combine personal collection with practical assistance before and after purchase.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('Typical services include order pickup, basic product advice, complaint intake, returns, and information about delivery options.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Terms and conditions of department stores' => [
                'intro' => t('These demonstration store terms summarise reservations, personal collection, payment, returns, and complaint handling at sample branches.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('They are intended only for product demonstration and require legal and operational adaptation before use by a real retailer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'Privacy policy' => [
                'intro' => t('This demonstration privacy policy explains how a sample online store may process account, order, communication, and technical data.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('A real policy must identify the controller, purposes, legal bases, retention periods, recipients, security measures, and all rights available to customers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'User consent policy' => [
                'intro' => t('Optional consent can be used for clearly described purposes such as personalised marketing or selected analytics.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'detail' => t('Consent must be voluntary, specific, informed, and easy to withdraw. Refusing optional consent must not prevent completion of an ordinary purchase.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
        ];
        $contentTranslationKeys = $contentTranslationKeysByArticleName[$articleName];
        $intro = $contentTranslationKeys['intro'];
        $heading = t('What you should know', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $detail = $contentTranslationKeys['detail'];

        return sprintf('<div class="gjs-text-ckeditor"><p>%s</p><h2>%s</h2><p>%s</p></div>', $intro, $heading, $detail);
    }

    private function createSearchArticleText(string $locale): string
    {
        $intro = t('Dina is preparing a comfortable home office and compares a monitor, headphones, and practical accessories before ordering.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $heading = t('A simple checklist before purchase', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $detail = t('She checks dimensions, compatibility, warranty conditions, and delivery options so every product fits the way she works.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        return sprintf('<div class="gjs-text-ckeditor"><p>%s</p><h2>%s</h2><p>%s</p></div>', $intro, $heading, $detail);
    }

    private function createForPressArticleText(string $locale, string $homepageUrl, string $categoryUrl): string
    {
        $intro = t('Welcome to the Demo shop press centre. Here you can find company information, product selections, visual materials, and contacts for media enquiries.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mediaContact = t('Media contact', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $mediaContactText = t('For interviews, comments, and background information, contact press@demo-shop.example.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $assets = t('Product materials', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $assetsText = t('The selections below demonstrate how product recommendations can be embedded directly into editorial content.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $markets = t('3 language versions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $categories = t('Electronics and home', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $support = t('Customer care every workday', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $aboutHeading = t('About Demo shop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $aboutText = t('Demo shop presents a complete sample e-commerce experience, from product discovery and content to ordering and after-sales care.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $aboutImageAlt = t('Demo shop presentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $factsHeading = t('Key facts', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $videoHeading = t('Video materials', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $storesHeading = t('Stores and locations', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $imagesHeading = t('Downloadable images', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $downloadableImageAlt = t('Downloadable Demo shop press image', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $moreProducts = t('Explore electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        return str_replace(['    ', PHP_EOL], '', trim(<<<EOT
            <div class="gjs-text-ckeditor"><p>{$intro}</p></div>
            <div class="gjs-products" data-products="9177759,5964035">
                <div data-product="9177759" class="gjs-product"></div>
                <div data-product="5964035" class="gjs-product"></div>
            </div>
            <div class="gjs-products" data-products="9177759,9176508,5965879P,532564,1532564,5960453">
                <div data-product="9177759" class="gjs-product"></div>
                <div data-product="9176508" class="gjs-product"></div>
                <div data-product="5965879P" class="gjs-product"></div>
                <div data-product="532564" class="gjs-product"></div>
                <div data-product="1532564" class="gjs-product"></div>
                <div data-product="5960453" class="gjs-product"></div>
            </div>
            <div class="row">
                <div class="column"><div class="gjs-text-ckeditor" style="text-align: center"><strong>{$mediaContact}</strong><br />{$mediaContactText}</div></div>
                <div class="column"><div class="gjs-text-ckeditor" style="text-align: center"><strong>{$assets}</strong><br />{$assetsText}</div></div>
            </div>
            <div class="row">
                <div class="column" style="text-align: center"><div class="gjs-text-ckeditor">{$markets}</div></div>
                <div class="column" style="text-align: center"><div class="gjs-text-ckeditor">{$categories}</div></div>
                <div class="column" style="text-align: center"><div class="gjs-text-ckeditor">{$support}</div></div>
            </div>
            <div class="gjs-text-ckeditor"><h2>{$aboutHeading}</h2></div>
            <div class="gjs-text-with-image gjs-text-with-image-float-left">
                <img src="{$homepageUrl}content/images/blogArticle/default/600.jpg" class="image" alt="{$aboutImageAlt}" />
                <div class="gjs-text-ckeditor text">{$aboutText}</div>
            </div>
            <div class="gjs-text-ckeditor"><h3>{$factsHeading}</h3></div>
            <div class="row" role="list">
                <div class="column" role="presentation">
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$markets}</div></div>
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$categories}</div></div>
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$support}</div></div>
                </div>
                <div class="column" role="presentation">
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$mediaContact}</div></div>
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$assets}</div></div>
                    <div role="listitem"><div class="gjs-text-ckeditor text">{$assetsText}</div></div>
                </div>
            </div>
            <div class="gjs-text-ckeditor"><h4>{$videoHeading}</h4></div>
            <video src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3R5bGU9ImZpbGw6IHJnYmEoMCwwLDAsMC4xNSk7IHRyYW5zZm9ybTogc2NhbGUoMC43NSkiPgogICAgICAgIDxwYXRoIGQ9Ik04LjUgMTMuNWwyLjUgMyAzLjUtNC41IDQuNSA2SDVtMTYgMVY1YTIgMiAwIDAgMC0yLTJINWMtMS4xIDAtMiAuOS0yIDJ2MTRjMCAxLjEuOSAyIDIgMmgxNGMxLjEgMCAyLS45IDItMnoiPjwvcGF0aD4KICAgICAgPC9zdmc+" controls></video>
            <div class="gjs-text-ckeditor"><h5>{$storesHeading}</h5></div>
            <iframe src="https://maps.google.com/maps?&z=1&t=q&output=embed" style="height: 350px; width: 100%; border: 0"></iframe>
            <div class="gjs-text-ckeditor"><h6>{$imagesHeading}</h6></div>
            <img src="{$homepageUrl}content/images/blogArticle/default/601.jpg" class="image-position-left" alt="{$downloadableImageAlt}" />
            <a class="gjs-button-link button-link-position-center" title="{$moreProducts}" href="{$categoryUrl}"><div class="gjs-text-ckeditor text">{$moreProducts}</div></a>
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
