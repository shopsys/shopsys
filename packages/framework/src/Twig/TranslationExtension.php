<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslationExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('transHtml', $this->transHtml(...), [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'translation';
    }

    /**
     * Similar to "trans" filter, the message is not escaped in html but all translation arguments are
     *
     * Helpful for protection from XSS when providing user input as translation argument
     *
     * @param \Twig\Environment $twig
     * @param string $message
     * @param array $arguments
     * @param string|null $translationDomain
     * @param string|null $locale
     * @return string
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
     *
     * @param \Twig\Environment $twig
     * @param array $elements
     * @return array
     */
    protected function getEscapedElements(Environment $twig, array $elements)
    {
        $defaultEscapeFilterCallable = $twig->getFilter('escape')->getCallable();
        $escapedElements = [];

        foreach ($elements as $key => $element) {
            $escapedElements[$key] = $defaultEscapeFilterCallable($twig, $element);
        }

        return $escapedElements;
    }
}
