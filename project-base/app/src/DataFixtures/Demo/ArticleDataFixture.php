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
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        private readonly ArticleFacade $articleFacade,
        private readonly ArticleDataFactory $articleDataFactory,
        private readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
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
        $homepageUrl = $this->generateUrlForHomepageOnDomain($domainConfig->getId());

        return [
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
                                allowfullscreen="allowfullscreen"
                                src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3R5bGU9ImZpbGw6IHJnYmEoMCwwLDAsMC4xNSk7IHRyYW5zZm9ybTogc2NhbGUoMC43NSkiPgogICAgICAgIDxwYXRoIGQ9Ik04LjUgMTMuNWwyLjUgMyAzLjUtNC41IDQuNSA2SDVtMTYgMVY1YTIgMiAwIDAgMC0yLTJINWMtMS4xIDAtMiAuOS0yIDJ2MTRjMCAxLjEuOSAyIDIgMmgxNGMxLjEgMCAyLS45IDItMnoiPjwvcGF0aD4KICAgICAgPC9zdmc+"
                                controls="controls"
                                id="ijwm64"
                                style="margin: 0px 0px 0 0px"
                            ></video>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column">
                            <div class="gjs-text-ckeditor" data-gjs-type="text">
                                <h5 id="i4utlb" draggable="true">H5 Mapson</h5>
                            </div>
                            <iframe
                                frameborder="0"
                                id="in1zyi"
                                src="https://maps.google.com/maps?&z=1&t=q&output=embed"
                                style="height: 350px; width: 100%"
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
                        backgroundcolor="#00C8B7"
                        class="gjs-button-link button-link-position-center"
                        title="More products"
                        href="' . $this->generateUrlForCategoryOnDomain(CategoryDataFixture::CATEGORY_ELECTRONICS, $domainConfig->getId()) . '"
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
        $articleData = $this->articleDataFactory->create($domainId);
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

    /**
     * @param int $domainId
     * @return string
     */
    private function generateUrlForHomepageOnDomain(int $domainId): string
    {
        $router = $this->domainRouterFactory->getRouter($domainId);

        return $router->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param string $categoryReferenceName
     * @param int $domainId
     * @return string
     */
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
}
