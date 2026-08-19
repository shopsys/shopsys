<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DemoDataFactory;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

final class BlogArticleContentFactory
{
    public const string ARTICLE_GROUP_GENERAL = 'general';
    public const string ARTICLE_GROUP_BUYING_GUIDE = 'buying-guide';
    public const string ARTICLE_GROUP_INSPIRATION = 'inspiration';
    public const string ARTICLE_GROUP_PRODUCT_NEWS = 'product-news';
    public const string ARTICLE_GROUP_CARE = 'care';
    public const string ARTICLE_GROUP_TECHNOLOGY = 'technology';

    public function createTitle(string $articleGroup, int $index, string $locale): string
    {
        return match ($articleGroup) {
            self::ARTICLE_GROUP_GENERAL => $this->createGeneralTitle($index, $locale),
            self::ARTICLE_GROUP_BUYING_GUIDE => $this->createBuyingGuideTitle($index, $locale),
            self::ARTICLE_GROUP_INSPIRATION => $this->createInspirationTitle($index, $locale),
            self::ARTICLE_GROUP_PRODUCT_NEWS => $this->createProductNewsTitle($index, $locale),
            self::ARTICLE_GROUP_CARE => $this->createCareTitle($index, $locale),
            self::ARTICLE_GROUP_TECHNOLOGY => $this->createTechnologyTitle($index, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown blog article group "%s".', $articleGroup)),
        };
    }

    private function createGeneralTitle(int $index, string $locale): string
    {
        $topic = match ($index % 5) {
            0 => t('Energy-efficient home', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('Online shopping', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            2 => t('Product care', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            3 => t('Smart technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            4 => t('Family entertainment', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown general blog article index "%d".', $index)),
        };
        $parameters = ['%topic%' => $topic];

        return match (intdiv($index, 5)) {
            0 => t('%topic%: practical advice from our experts', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('%topic%: common mistakes and how to avoid them', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            2 => t('%topic%: answers to the most frequent questions', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown general blog article index "%d".', $index)),
        };
    }

    private function createBuyingGuideTitle(int $index, string $locale): string
    {
        $topic = match ($index % 5) {
            0 => t('Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('Headphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            2 => t('Laptop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            3 => t('Coffee machine', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            4 => t('Camera', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown buying guide blog article index "%d".', $index)),
        };
        $parameters = ['%topic%' => $topic];

        return match (intdiv($index, 5)) {
            0 => t('%topic%: how to choose', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('%topic%: what to look for', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            2 => t('%topic%: five practical tips', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown buying guide blog article index "%d".', $index)),
        };
    }

    private function createInspirationTitle(int $index, string $locale): string
    {
        $topic = match ($index % 5) {
            0 => t('Home cinema', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('Smart kitchen', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            2 => t('Home office', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            3 => t('Better sleep', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            4 => t('Weekend photography', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown inspiration blog article index "%d".', $index)),
        };
        $parameters = ['%topic%' => $topic];

        return match (intdiv($index, 5)) {
            0 => t('%topic%: ideas for a more comfortable home', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('%topic%: simple upgrades that make a difference', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            2 => t('%topic%: inspiration for every day', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown inspiration blog article index "%d".', $index)),
        };
    }

    private function createProductNewsTitle(int $index, string $locale): string
    {
        return match ($index) {
            0 => t('Three new televisions worth knowing about', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('What is new in wireless headphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown product news blog article index "%d".', $index)),
        };
    }

    private function createCareTitle(int $index, string $locale): string
    {
        return match ($index) {
            0 => t('How to clean a television screen safely', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('Simple maintenance that extends appliance life', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown care blog article index "%d".', $index)),
        };
    }

    private function createTechnologyTitle(int $index, string $locale): string
    {
        return match ($index) {
            0 => t('What artificial intelligence changes in home electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1 => t('OLED, QLED, and Mini LED explained', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            default => throw new InvalidArgumentException(sprintf('Unknown technology blog article index "%d".', $index)),
        };
    }

    public function createDescription(string $articleTitle, string $articleGroup, string $locale): string
    {
        $translatedContent = $this->getTranslatedContent($articleGroup, $locale);
        $parameters = ['%articleTitle%' => $articleTitle];

        $intro = t('This guide takes a practical look at %articleTitle% and focuses on decisions that make a real difference in everyday use.', $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $firstHeading = $translatedContent['firstHeading'];
        $firstParagraph = $translatedContent['firstParagraph'];
        $secondHeading = $translatedContent['secondHeading'];
        $secondParagraph = $translatedContent['secondParagraph'];
        $productsHeading = t('Products worth comparing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productsParagraph = t('Use the related products as a starting point and compare the parameters that matter for your situation.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        return str_replace(['    ', PHP_EOL], '', trim(<<<EOT
            <div class="gjs-text-ckeditor">
                <p>{$intro}</p>
                <h2>{$firstHeading}</h2>
                <p>{$firstParagraph}</p>
                <h2>{$secondHeading}</h2>
                <p>{$secondParagraph}</p>
                <h2>{$productsHeading}</h2>
                <p>{$productsParagraph}</p>
            </div>
            <div class="gjs-products" data-products="9177759,5965879P,9184449,9176544M,7700768">
                <div class="gjs-product" data-product="9177759"></div>
                <div class="gjs-product" data-product="5965879P"></div>
                <div class="gjs-product" data-product="9184449"></div>
                <div class="gjs-product" data-product="9176544M"></div>
                <div class="gjs-product" data-product="7700768"></div>
            </div>
        EOT));
    }

    /**
     * @return array{firstHeading: string, firstParagraph: string, secondHeading: string, secondParagraph: string}
     */
    private function getTranslatedContent(string $articleGroup, string $locale): array
    {
        return match ($articleGroup) {
            self::ARTICLE_GROUP_GENERAL => [
                'firstHeading' => t('Start with your everyday needs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'firstParagraph' => t('Consider how the product or idea will fit into your daily routine, available space, and household budget. Clear priorities make every following decision easier.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondHeading' => t('Choose a solution that will last', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondParagraph' => t('Look beyond the first impression and compare durability, running costs, service options, and features you will genuinely use.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            self::ARTICLE_GROUP_BUYING_GUIDE => [
                'firstHeading' => t('Set priorities before comparing models', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'firstParagraph' => t('Write down the features you need, the limits of your space, and a realistic budget. This quickly separates useful options from unnecessary extras.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondHeading' => t('Check the details that affect daily use', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondParagraph' => t('Compare dimensions, controls, connectivity, energy consumption, and warranty conditions before making the final choice.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            self::ARTICLE_GROUP_INSPIRATION => [
                'firstHeading' => t('Build the idea around your space', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'firstParagraph' => t('Begin with the room, the people who use it, and the atmosphere you want to create. A good solution should feel natural rather than forced.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondHeading' => t('Prefer improvements you will use every day', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondParagraph' => t('Small, thoughtful changes often bring more comfort than a complete redesign. Focus on practical details that simplify familiar routines.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            self::ARTICLE_GROUP_PRODUCT_NEWS => [
                'firstHeading' => t('What is genuinely new', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'firstParagraph' => t('The most useful innovations improve picture, sound, comfort, or energy efficiency without making everyday operation more complicated.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondHeading' => t('Who will benefit most', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondParagraph' => t('New features make sense when they solve a real need. Compare them with the equipment you already own and the way you use it.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            self::ARTICLE_GROUP_CARE => [
                'firstHeading' => t('Use the right tools and a gentle routine', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'firstParagraph' => t('Follow the manufacturer instructions, disconnect the device when necessary, and avoid aggressive cleaners or excessive moisture.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondHeading' => t('Prevent common problems', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondParagraph' => t('Regular light maintenance, careful storage, and timely replacement of worn accessories help products work reliably for longer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            self::ARTICLE_GROUP_TECHNOLOGY => [
                'firstHeading' => t('How the technology works', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'firstParagraph' => t('Modern features combine sensors, software, and efficient hardware. Understanding their purpose makes technical specifications easier to compare.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondHeading' => t('What it means in everyday use', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'secondParagraph' => t('The best technology saves time, reduces energy consumption, or improves comfort while remaining simple to control and maintain.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            default => throw new InvalidArgumentException(sprintf('Unknown blog article group "%s".', $articleGroup)),
        };
    }

    public function createMainArticleDescription(string $firstDomainUrl): string
    {
        $description = $this->createMainArticleGuide($firstDomainUrl) . $this->createMainArticleProducts();

        return str_replace(['    ', PHP_EOL], '', trim($description));
    }

    private function createMainArticleGuide(string $firstDomainUrl): string
    {
        return <<<EOT
            <div class="gjs-text-ckeditor">
                <p>
                    The right television should fit your room, viewing distance, and the content you watch most often. Dina started by noting where the TV would stand, how bright the room was, and which devices she wanted to connect.
                </p>

                <ul>
                    <li>Measure the distance between the sofa and the screen.</li>
                    <li>Check how much daylight reaches the room.</li>
                    <li>Count the game consoles, sound systems, and other HDMI devices you use.</li>
                </ul>

                <h2>Start with screen size and viewing distance</h2>
                <p>
                    A larger screen is not always better. At a distance of around 1.5 metres, a 32-inch television is comfortable for everyday viewing. For a sofa 2 to 2.5 metres away, look at 43- to 50-inch models. A 55-inch screen or larger works best when you can sit at least 2.5 metres away and want a more cinematic experience.
                </p>

                <h3>Leave space around the television</h3>
                <p>
                    Remember to include the stand, cables, and ventilation in your measurements. If the television will be mounted on a wall, place the centre of the screen close to eye level when seated.
                </p>

                <h2>Choose picture quality for your favourite content</h2>
                <p>
                    Full HD remains a practical choice for smaller screens and regular television broadcasts. For larger screens, films, modern game consoles, and streaming services, <strong>4K resolution</strong> provides noticeably finer detail. HDR support improves contrast and preserves detail in both bright highlights and dark scenes.
                </p>

                <h3>Pay attention to brightness and reflections</h3>
                <p>
                    A brighter panel and an effective anti-reflective surface are useful in a sunny living room. In a darker room, black levels and consistent backlighting have a greater effect on perceived image quality.
                </p>

                <h2>Check connections and everyday features</h2>
                <p>
                    Choose a model with enough HDMI inputs for your set-top box, console, and soundbar. Wi-Fi, Bluetooth, and built-in streaming applications make everyday use easier. Gamers should also check input lag, refresh rate, and support for modern HDMI gaming features.
                </p>

                <p>
                    <strong>TIP:</strong> <a href="{$firstDomainUrl}" id="ieevs4" tabindex="0">Browse all televisions and compare their parameters</a> before making the final choice.
                </p>
            </div>
        EOT;
    }

    private function createMainArticleProducts(): string
    {
        return <<<'EOT'
            <div class="gjs-text-ckeditor">
                <h2>A practical choice for smaller rooms</h2>
                <p>
                    A compact 32-inch television is a good fit for a bedroom, kitchen, or smaller living room. It provides a comfortable picture without dominating the space and still offers the connections needed for common accessories.
                </p>
            </div>

            <div class="gjs-products" data-products="9176508">
                <div data-product="9176508" data-product-name="32&quot; Philips 32PFL4308" class="gjs-product"></div>
            </div>

            <div class="gjs-text-ckeditor">
                <h2>Compare popular TV sizes</h2>
                <p>
                    Compare compact, mid-size, and larger televisions side by side. Besides the diagonal size, check the stand dimensions, resolution, available connections, and whether the television fits the way you watch films, television, and games.
                </p>
            </div>

            <div class="gjs-products" data-products="9177759,9176508,5965879P">
                <div data-product="9177759" data-product-name="22&quot; Sencor SLE 22F46DM4 HELLO KITTY" class="gjs-product"></div>
                <div data-product="9176508" data-product-name="32&quot; Philips 32PFL4308" class="gjs-product"></div>
                <div data-product="5965879P" data-product-name="47&quot; LG 47LA790V (FHD)" class="gjs-product"></div>
            </div>
        EOT;
    }
}
