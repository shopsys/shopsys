<?php

namespace Shopsys\FrameworkBundle\Model\Script;

use Doctrine\ORM\EntityManagerInterface;

class ScriptRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getScriptRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Script::class);
    }
    
    public function getById(int $scriptId): \Shopsys\FrameworkBundle\Model\Script\Script
    {
        $script = $this->getScriptRepository()->find($scriptId);

        if ($script === null) {
            throw new \Shopsys\FrameworkBundle\Model\Script\Exception\ScriptNotFoundException('Script with ID ' . $scriptId . ' does not exist.');
        }

        return $script;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Script\Script[]
     */
    public function getAll(): array
    {
        return $this->getScriptRepository()->findAll();
    }

    public function getAllQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->getScriptRepository()->createQueryBuilder('s');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Script\Script[]
     */
    public function getScriptsByPlacement(string $placement): array
    {
        return $this->getScriptRepository()->findBy(['placement' => $placement]);
    }
}
