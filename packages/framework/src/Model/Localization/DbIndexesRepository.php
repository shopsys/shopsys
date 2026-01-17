<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;

class DbIndexesRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    public function updateProductTranslationNameIndexForLocaleAndCollation(string $locale, string $collation): void
    {
        $this->em->getConnection()->executeStatement(
            'CREATE INDEX IF NOT EXISTS product_translations_name_' . $locale . '_idx
                ON product_translations (name COLLATE "' . $collation . '") WHERE locale = \':locale\'',
            [
                'locale' => $locale,
            ],
            [
                'locale' => Types::STRING,
            ],
        );
    }
}
