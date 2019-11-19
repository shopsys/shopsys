<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Javascript\Compiler\Constant;

use Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompiler;
use Tests\App\Test\FunctionalTestCase;

class JsConstantCompilerPassTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Javascript\Compiler\Constant\JsConstantCompilerPass
     * @inject
     */
    private $jsConstantCompilerPass;

    public function testJsCompilerReplacesDefinedConstants()
    {
        $content = file_get_contents(__DIR__ . '/testDefinedConstant.js');
        $result = $this->getJsCompiler()->compile($content);

        $expectedResult = trim(file_get_contents(__DIR__ . '/testDefinedConstant.expected.js'));

        $this->assertSame($expectedResult, $result);
    }

    public function testJsCompilerReplacesClassNames()
    {
        $content = file_get_contents(__DIR__ . '/testClassName.js');
        $result = $this->getJsCompiler()->compile($content);

        $expectedResult = trim(file_get_contents(__DIR__ . '/testClassName.expected.js'));

        $this->assertSame($expectedResult, $result);
    }

    public function testJsCompilerFailsOnUndefinedConstant()
    {
        $content = file_get_contents(__DIR__ . '/testUndefinedConstant.js');

        $this->expectException(\Shopsys\FrameworkBundle\Component\Javascript\Compiler\Constant\Exception\ConstantNotFoundException::class);
        $this->getJsCompiler()->compile($content);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompiler
     */
    private function getJsCompiler()
    {
        return new JsCompiler([
            $this->jsConstantCompilerPass,
        ]);
    }
}
