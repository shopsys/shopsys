<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\Query;
use Shopsys\FrameworkBundle\Component\Doctrine\CollationOrderByWalker;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class HintsHelper
{
    public function __construct(
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultHints(): array
    {
        return [
            Query::HINT_CUSTOM_OUTPUT_WALKER => SortableNullsWalker::class,
            Query::HINT_CUSTOM_TREE_WALKERS => [CollationOrderByWalker::class],
            'collation' => $this->localization->getCollationByLocale($this->localization->getCurrentLocaleForTranslatableEntities()),
        ];
    }
}
