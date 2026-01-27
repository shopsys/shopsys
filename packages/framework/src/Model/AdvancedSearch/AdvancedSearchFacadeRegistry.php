<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class AdvancedSearchFacadeRegistry
{
    /**
     * @var array<string,\Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade>
     */
    protected array $facades = [];

    /**
     * @param iterable<\Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade> $facades
     */
    public function __construct(
        #[TaggedIterator('shopsys.advanced_search_facade')]
        iterable $facades,
    ) {
        foreach ($facades as $facade) {
            $this->facades[$facade::getEntityType()] = $facade;
        }
    }

    /**
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade
     */
    public function get(string $type): AbstractAdvancedSearchFacade
    {
        return $this->facades[$type] ?? throw new InvalidArgumentException('Unknown type: ' . $type);
    }
}
