<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Component\Javascript\Compiler\Translator;

use Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompiler;
use Shopsys\FrameworkBundle\Component\Javascript\Compiler\Translator\JsTranslatorCompilerPass;
use Tests\ShopBundle\Test\FunctionalTestCase;

class JsTranslatorCompilerPassTest extends FunctionalTestCase
{
    /**
     * @var JsTranslatorCompilerPass
     * @inject
     */
    private $jsTranslatorCompilerPass;

    public function testProcess()
    {
        /** @var \Shopsys\FrameworkBundle\Component\Translation\Translator $translator */
        $translator = $this->getContainer()->get('translator');

        $translator->setLocale('testLocale');
        $translator->getCatalogue()->add([
            'source value' => 'translated value',
            'source %param%' => 'translated %param%',
        ]);

        $jsCompiler = new JsCompiler([
            $this->jsTranslatorCompilerPass,
        ]);

        $content = <<<EOD
var trans = Shopsys.translator.trans('source value');
var transParam = Shopsys.translator.trans('source' + ' ' + '%param%', { '%param%' : 'value' }, 'domain');
var transChoice = Shopsys.translator.transChoice('source value' );
var transUntranslated = Shopsys.translator.trans('untranslated source value');
var transChoiceUntranslated = Shopsys.translator.transChoice('untranslated source value');
EOD;

        $result = $jsCompiler->compile($content);

        $expectedResult = <<<EOD
var trans = Shopsys.translator.trans ( "translated value" );
var transParam = Shopsys.translator.trans ( "translated %param%", { '%param%' : 'value' }, 'domain' );
var transChoice = Shopsys.translator.transChoice ( "translated value" );
var transUntranslated = Shopsys.translator.trans ( "untranslated source value" );
var transChoiceUntranslated = Shopsys.translator.transChoice ( "untranslated source value" );
EOD;

        $this->assertSame($expectedResult, $result);
    }
}
