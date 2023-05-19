<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

class SqlQuoter
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param string[] $identifiers
     * @return string[]
     */
    public function quoteIdentifiers(array $identifiers)
    {
        return array_map(
            function ($identifier) {
                return $this->quoteIdentifier($identifier);
            },
            $identifiers,
        );
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $this->em->getConnection()->quoteIdentifier($identifier);
    }

    /**
     * @param mixed $input
     * @param int|null $type
     * @return string
     */
    public function quote($input, $type = null)
    {
        return $this->em->getConnection()->quote($input, $type);
    }
}
