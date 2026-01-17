<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Override;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Tests\App\Test\FunctionalTestCase;
use Twig\Environment;

class AutoCardGroupingRenderingTest extends FunctionalTestCase
{
    private const string XPATH_CARD = '//div[contains(concat(" ", normalize-space(@class), " "), " card ")]';
    private const string XPATH_CARD_BODY = '//div[contains(@class, "card-body")]';
    private const string XPATH_CARD_BODY_ROWS = self::XPATH_CARD_BODY . '//div[contains(@class, "row")]';
    private const string XPATH_HIDDEN_FIELDS_NO_TOKEN = '//input[@type="hidden"][@id and not(contains(@id, "_token"))]';

    /**
     * @inject
     */
    private Environment $twig;

    /**
     * @inject
     */
    private FormFactoryInterface $formFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
    }

    public function testConsecutiveFieldsAreGroupedIntoSingleCard(): void
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('email', TextType::class)
            ->add('phone', TextType::class)
            ->getForm();

        $html = $this->renderForm($form);
        $xpath = $this->createXpath($html);

        $cards = $this->queryCards($xpath);
        $this->assertCount(1, $cards, 'Expected exactly 1 auto-card wrapper for 3 consecutive fields');

        $cardBody = $xpath->query(self::XPATH_CARD . self::XPATH_CARD_BODY);
        $this->assertCount(1, $cardBody, 'Expected card to have card-body');

        $rowsInCard = $xpath->query(self::XPATH_CARD_BODY_ROWS);
        $this->assertCount(3, $rowsInCard, 'Expected exactly 3 form rows in card body');
    }

    public function testRendersInOwnCardBreaksAutoGrouping(): void
    {
        $form = $this->createFormBuilder()
            ->add('field1', TextType::class)
            ->add('field2', TextType::class)
            ->add('group', GroupType::class, ['label' => 'Group Label'])
            ->add('field3', TextType::class)
            ->add('field4', TextType::class)
            ->getForm();

        $html = $this->renderForm($form);
        $xpath = $this->createXpath($html);

        $cards = $this->queryCards($xpath);
        $this->assertCount(3, $cards, 'Expected 3 cards: 2 auto-cards (field1+field2, field3+field4) and 1 GroupType card');

        $firstCardRows = $xpath->query('(' . self::XPATH_CARD . ')[1]//div[contains(@class, "row")]');
        $this->assertCount(2, $firstCardRows, 'Expected first card to contain exactly 2 fields');
    }

    public function testInvisibleFieldsDontBreakAutoGrouping(): void
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('hidden1', HiddenType::class)
            ->add('email', TextType::class)
            ->add('hidden2', HiddenType::class)
            ->add('phone', TextType::class)
            ->getForm();

        $html = $this->renderForm($form);
        $xpath = $this->createXpath($html);

        $cards = $this->queryCards($xpath);
        $this->assertCount(1, $cards, 'Hidden fields should not break auto-grouping');

        $hiddenInputs = $this->queryHiddenFieldsExcludingToken($xpath);
        $this->assertCount(2, $hiddenInputs, 'Expected exactly 2 hidden fields to be rendered');
    }

    public function testPositionOptionMaintainsCorrectOrderWithAutoGrouping(): void
    {
        $form = $this->createFormBuilder()
            ->add('last', TextType::class, ['position' => 'last'])
            ->add('first', TextType::class, ['position' => 'first'])
            ->add('middle', TextType::class)
            ->getForm();

        $html = $this->renderForm($form);
        $xpath = $this->createXpath($html);

        $cards = $this->queryCards($xpath);
        $this->assertCount(1, $cards, 'Expected single auto-card for all positioned fields');

        $formRows = $xpath->query(self::XPATH_CARD_BODY_ROWS);
        $this->assertCount(3, $formRows, 'Expected exactly 3 form rows');

        $firstInFirstPosition = $xpath->query('(' . self::XPATH_CARD_BODY_ROWS . ')[1]//label[contains(@for, "first")]');
        $this->assertCount(1, $firstInFirstPosition, 'Field "first" should be in first position');

        $middleInSecondPosition = $xpath->query('(' . self::XPATH_CARD_BODY_ROWS . ')[2]//label[contains(@for, "middle")]');
        $this->assertCount(1, $middleInSecondPosition, 'Field "middle" should be in second position');

        $lastInThirdPosition = $xpath->query('(' . self::XPATH_CARD_BODY_ROWS . ')[3]//label[contains(@for, "last")]');
        $this->assertCount(1, $lastInThirdPosition, 'Field "last" should be in third position');
    }

    public function testFormWithOnlyOwnCardFieldsHasNoAutoCards(): void
    {
        $form = $this->createFormBuilder()
            ->add('group1', GroupType::class, ['label' => 'Group 1'])
            ->add('group2', GroupType::class, ['label' => 'Group 2'])
            ->add('group3', GroupType::class, ['label' => 'Group 3'])
            ->getForm();

        $html = $this->renderForm($form);
        $xpath = $this->createXpath($html);

        $allCards = $this->queryCards($xpath);
        $this->assertCount(3, $allCards, 'Expected exactly 3 GroupType cards');

        $group1Card = $xpath->query('//div[contains(@class, "card")]//h3[contains(text(), "Group 1")]');
        $this->assertCount(1, $group1Card, 'Group 1 should be rendered in a card');

        $group2Card = $xpath->query('//div[contains(@class, "card")]//h3[contains(text(), "Group 2")]');
        $this->assertCount(1, $group2Card, 'Group 2 should be rendered in a card');

        $group3Card = $xpath->query('//div[contains(@class, "card")]//h3[contains(text(), "Group 3")]');
        $this->assertCount(1, $group3Card, 'Group 3 should be rendered in a card');
    }

    public function testExplicitRendersInOwnCardFalseIsAutoGrouped(): void
    {
        $form = $this->createFormBuilder()
            ->add('field1', TextType::class, ['renders_in_own_card' => false])
            ->add('field2', TextType::class, ['renders_in_own_card' => false])
            ->add('field3', TextType::class, ['renders_in_own_card' => false])
            ->getForm();

        $html = $this->renderForm($form);
        $xpath = $this->createXpath($html);

        $cards = $this->queryCards($xpath);
        $this->assertCount(1, $cards, 'Explicit renders_in_own_card: false should create auto-card');
    }

    public function testSingleFieldCreatesAutoCard(): void
    {
        $form = $this->createFormBuilder()
            ->add('alone', TextType::class)
            ->getForm();

        $html = $this->renderForm($form);
        $xpath = $this->createXpath($html);

        $cards = $this->queryCards($xpath);
        $this->assertCount(1, $cards, 'Single field should be wrapped in auto-card');
    }

    public function testAlternatingAutoCardAndOwnCardFields(): void
    {
        $form = $this->createFormBuilder()
            ->add('field1', TextType::class)
            ->add('group1', GroupType::class, ['label' => 'Group 1'])
            ->add('field2', TextType::class)
            ->add('group2', GroupType::class, ['label' => 'Group 2'])
            ->add('field3', TextType::class)
            ->getForm();

        $html = $this->renderForm($form);
        $xpath = $this->createXpath($html);

        $cards = $this->queryCards($xpath);
        $this->assertCount(5, $cards, 'Expected 5 cards: 3 auto-cards (field1, field2, field3) and 2 GroupType cards');
    }

    /**
     * @return \Symfony\Component\Form\FormBuilderInterface<mixed>
     */
    private function createFormBuilder(): FormBuilderInterface
    {
        return $this->formFactory->createBuilder();
    }

    private function renderForm(FormInterface $form): string
    {
        $view = $form->createView();

        $template = $this->twig->createTemplate(
            "{% form_theme form '@ShopsysAdministration/form/theme.html.twig' %}\n{{ form_widget(form) }}",
        );

        return $template->render(['form' => $view]);
    }

    private function createXpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return new DOMXPath($dom);
    }

    private function queryCards(DOMXPath $xpath): DOMNodeList
    {
        return $xpath->query(self::XPATH_CARD);
    }

    private function queryHiddenFieldsExcludingToken(DOMXPath $xpath): DOMNodeList
    {
        return $xpath->query(self::XPATH_HIDDEN_FIELDS_NO_TOKEN);
    }
}
