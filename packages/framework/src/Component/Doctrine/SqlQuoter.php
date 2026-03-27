<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

class SqlQuoter
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param string[] $identifiers
     * @return string[]
     */
    public function quoteIdentifiers(array $identifiers): array
    {
        return array_map(
            function ($identifier) {
                return $this->quoteIdentifier($identifier);
            },
            $identifiers,
        );
    }

    public function quoteIdentifier(string $identifier): string
    {
        return $this->em->getConnection()->quoteIdentifier($identifier);
    }

    public function quote(string $input): string
    {
        return $this->em->getConnection()->quote($input);
    }
}
