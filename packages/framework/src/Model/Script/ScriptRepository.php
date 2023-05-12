<?php

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
    protected function getScriptRepository()
    {
        return $this->em->getRepository(Script::class);
    }

    /**
     * @param int $scriptId
     * @return \Shopsys\FrameworkBundle\Model\Script\Script
     */
    public function getById($scriptId)
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
    public function getAll()
    {
        return $this->getScriptRepository()->findAll();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder()
    {
        return $this->getScriptRepository()->createQueryBuilder('s');
    }

    /**
     * @param string $placement
     * @return \Shopsys\FrameworkBundle\Model\Script\Script[]
     */
    public function getScriptsByPlacement($placement)
    {
        return $this->getScriptRepository()->findBy(['placement' => $placement]);
    }
}
