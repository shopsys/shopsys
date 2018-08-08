<?php

namespace Shopsys\FrameworkBundle\Twig\Javascript;

use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Twig_Extension;
use Twig_SimpleFunction;

class JavascriptExtension extends Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Twig\Javascript\JavascriptCompilerService
     */
    private $javascriptCompilerService;

    public function __construct(JavascriptCompilerService $javascriptCompilerService)
    {
        $this->javascriptCompilerService = $javascriptCompilerService;
    }
    
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('importJavascripts', [$this, 'renderJavascripts'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string|array $javascripts
     */
    public function renderJavascripts($javascripts): string
    {
        $javascriptsArray = Utils::mixedToArray($javascripts);

        $javascriptLinks = $this->javascriptCompilerService->generateCompiledFiles($javascriptsArray);

        return $this->getHtmlJavascriptImports($javascriptLinks);
    }

    private function getHtmlJavascriptImports(array $javascriptLinks): string
    {
        $html = '';
        foreach ($javascriptLinks as $javascriptLink) {
            $html .= "\n" . '<script type="text/javascript" src="' . htmlspecialchars($javascriptLink, ENT_QUOTES) . '"></script>';
        }

        return $html;
    }

    public function getName(): string
    {
        return 'javascript_extension';
    }
}
