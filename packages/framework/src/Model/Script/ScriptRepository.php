<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Script;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Script\Exception\ScriptNotFoundException;

class ScriptRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getScriptRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Script::class);
    }

    /**
     * @param int $scriptId
     * @return \Shopsys\FrameworkBundle\Model\Script\Script
     */
    public function getById($scriptId): \Shopsys\FrameworkBundle\Model\Script\Script
    {
        $script = $this->getScriptRepository()->find($scriptId);

        if ($script === null) {
            throw new ScriptNotFoundException('Script with ID ' . $scriptId . ' does not exist.');
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

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->getScriptRepository()->createQueryBuilder('s');
    }

    /**
     * @param string $placement
     * @return \Shopsys\FrameworkBundle\Model\Script\Script[]
     */
    public function getScriptsByPlacement($placement): array
    {
        return $this->getScriptRepository()->findBy(['placement' => $placement]);
    }
}
