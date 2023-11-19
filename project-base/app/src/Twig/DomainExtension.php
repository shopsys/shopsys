<?php

declare(strict_types=1);

namespace App\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Twig\DomainExtension as BaseDomainExtension;
use Twig\TwigFunction;

class DomainExtension extends BaseDomainExtension
{
    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return array_merge(parent::getFunctions(), [
            new TwigFunction('getDomainUrlByLocale', [$this, 'getDomainUrlByLocale']),
        ]);
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getDomainUrlByLocale(string $locale): string
    {
        foreach ($this->domain->getAll() as $domain) {
            if ($domain->getLocale() === $locale) {
                return $domain->getUrl();
            }
        }

        throw new NoDomainSelectedException('Domain for locale `' . $locale . '` not found;');
    }
}
