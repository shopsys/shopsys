<?php

namespace Shopsys\FrameworkBundle\Model\Script;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Script\Exception\ScriptNotFoundException;

class ScriptRepository
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
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getScriptRepository(): EntityRepository
    {
        return $this->em->getRepository(Script::class);
    }

    /**
     * @param int $scriptId
     * @return \Shopsys\FrameworkBundle\Model\Script\Script
     */
    public function getById(int $scriptId): Script
    {
        $script = $this->getScriptRepository()->find($scriptId);

        if ($script === null) {
            throw new ScriptNotFoundException('Script with ID ' . $scriptId . ' does not exist.');
        }

        return $script;
    }

    /**
     * @return object[]
     */
    public function getAll(): array
    {
        return $this->getScriptRepository()->findAll();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getScriptRepository()->createQueryBuilder('s');
    }

    /**
     * @param string $placement
     * @return object[]
     */
    public function getScriptsByPlacement(string $placement): array
    {
        return $this->getScriptRepository()->findBy(['placement' => $placement]);
    }
}
