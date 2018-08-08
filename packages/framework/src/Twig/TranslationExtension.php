<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig_Environment;
use Twig_SimpleFilter;

class TranslationExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('transHtml', [$this, 'transHtml'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new Twig_SimpleFilter('transchoiceHtml', [$this, 'transchoiceHtml'], [
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
     * @see \Symfony\Bridge\Twig\Extension\TranslationExtension::trans()
     *
     * @param string $message
     * @param string|null $domain
     * @param string|null $locale
     */
    public function transHtml(Twig_Environment $twig, $message, array $arguments = [], $domain = null, $locale = null): string
    {
        $defaultTransCallable = $twig->getFilter('trans')->getCallable();
        $escapedArguments = $this->getEscapedElements($twig, $arguments);

        return $defaultTransCallable($message, $escapedArguments, $domain, $locale);
    }

    /**
     * Similar to "transchoice" filter, the message is not escaped in html but all translation arguments are
     *
     * Helpful for protection from XSS when providing user input as translation argument
     * @see \Symfony\Bridge\Twig\Extension\TranslationExtension::transchoice()
     *
     * @param string $message
     * @param int $count
     * @param string|null $domain
     * @param string|null $locale
     */
    public function transchoiceHtml(Twig_Environment $twig, $message, $count, array $arguments = [], $domain = null, $locale = null): string
    {
        $defaultTranschoiceCallable = $twig->getFilter('transchoice')->getCallable();
        $escapedArguments = $this->getEscapedElements($twig, $arguments);

        return $defaultTranschoiceCallable($message, $count, $escapedArguments, $domain, $locale);
    }

    /**
     * Escapes all elements in array with default twig "escape" filter
     */
    private function getEscapedElements(Twig_Environment $twig, array $elements): array
    {
        $defaultEscapeFilterCallable = $twig->getFilter('escape')->getCallable();
        $escapedElements = [];
        foreach ($elements as $key => $element) {
            $escapedElements[$key] = $defaultEscapeFilterCallable($twig, $element);
        }

        return $escapedElements;
    }
}
