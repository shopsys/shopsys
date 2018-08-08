<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

class SqlQuoter
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
            $identifiers
        );
    }

    public function quoteIdentifier(string $identifier): string
    {
        return $this->em->getConnection()->quoteIdentifier($identifier);
    }

    /**
     * @param mixed $input
     * @param string|null $type
     */
    public function quote($input, $type = null): string
    {
        return $this->em->getConnection()->quote($input, $type);
    }
}
