<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslationExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('transHtml', [$this, 'transHtml'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @return string
     */
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
     * @param \Twig\Environment $twig
     * @param string $message
     * @param array $arguments
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function transHtml(Environment $twig, string $message, array $arguments = [], ?string $domain = null, ?string $locale = null): string
    {
        $defaultTransCallable = $twig->getFilter('trans')->getCallable();
        $escapedArguments = $this->getEscapedElements($twig, $arguments);

        return $defaultTransCallable($message, $escapedArguments, $domain, $locale);
    }

    /**
     * Escapes all elements in array with default twig "escape" filter
     *
     * @param \Twig\Environment $twig
     * @param array $elements
     * @return array
     */
    protected function getEscapedElements(Environment $twig, array $elements): array
    {
        $defaultEscapeFilterCallable = $twig->getFilter('escape')->getCallable();
        $escapedElements = [];
        foreach ($elements as $key => $element) {
            $escapedElements[$key] = $defaultEscapeFilterCallable($twig, $element);
        }

        return $escapedElements;
    }
}
