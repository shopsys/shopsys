<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFacadeNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

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
        #[AutowireIterator('shopsys.advanced_search_facade')]
        iterable $facades,
    ) {
        foreach ($facades as $facade) {
            $this->facades[$facade::getEntityType()] = $facade;
        }
    }

    public function get(string $type): AbstractAdvancedSearchFacade
    {
        return $this->facades[$type] ?? throw new AdvancedSearchFacadeNotFoundException($type);
    }
}
