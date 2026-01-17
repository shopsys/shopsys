<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Runtime\EscaperRuntime;
use Twig\TwigFilter;

class TranslationExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('transHtml', $this->transHtml(...), [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function getName(): string
    {
        return 'translation';
    }

    /**
     * Similar to "trans" filter, the message is not escaped in html but all translation arguments are
     *
     * Helpful for protection from XSS when providing user input as translation argument
     *
     * @see \Symfony\Bridge\Twig\Extension\TranslationExtension::trans()
     */
    public function transHtml(
        Environment $twig,
        string $message,
        array $arguments = [],
        ?string $translationDomain = null,
        ?string $locale = null,
    ): string {
        $defaultTransCallable = $twig->getFilter('trans')->getCallable();
        $escapedArguments = $this->getEscapedElements($twig, $arguments);

        return $defaultTransCallable($message, $escapedArguments, $translationDomain, $locale);
    }

    /**
     * Escapes all elements in array with default twig "escape" filter
     */
    protected function getEscapedElements(Environment $twig, array $elements): array
    {
        $escapedElements = [];

        foreach ($elements as $key => $element) {
            $escapedElements[$key] = $twig->getRuntime(EscaperRuntime::class)->escape($element);
        }

        return $escapedElements;
    }
}
