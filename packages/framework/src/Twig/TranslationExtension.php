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
    public function getFilters()
    {
        return [
            new TwigFilter('transHtml', [$this, 'transHtml'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFilter('transchoiceHtml', [$this, 'transchoiceHtml'], [
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
     * @see \Symfony\Bridge\Twig\Extension\TranslationExtension::trans()
     *
     * @param \Twig\Environment $twig
     * @param string $message
     * @param array $arguments
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function transHtml(Environment $twig, $message, array $arguments = [], $domain = null, $locale = null)
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
     * @param \Twig\Environment $twig
     * @param string $message
     * @param int $count
     * @param array $arguments
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function transchoiceHtml(Environment $twig, $message, $count, array $arguments = [], $domain = null, $locale = null)
    {
        $defaultTranschoiceCallable = $twig->getFilter('transchoice')->getCallable();
        $escapedArguments = $this->getEscapedElements($twig, $arguments);

        return $defaultTranschoiceCallable($message, $count, $escapedArguments, $domain, $locale);
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
