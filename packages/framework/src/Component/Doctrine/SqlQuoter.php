<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

class SqlQuoter
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
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

    /**
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier(string $identifier): string
    {
        return $this->em->getConnection()->quoteIdentifier($identifier);
    }

    /**
     * @param mixed $input
     * @param int|null $type
     * @return string
     */
    public function quote(mixed $input, ?int $type = null): string
    {
        return $this->em->getConnection()->quote($input, $type);
    }
}
