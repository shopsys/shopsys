<?php

namespace Shopsys\FrameworkBundle\Model\Script;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ScriptFactory implements ScriptFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptData $data
     * @return \Shopsys\FrameworkBundle\Model\Script\Script
     */
    public function create(ScriptData $data): Script
    {
        $classData = $this->entityNameResolver->resolve(Script::class);

        return new $classData($data);
    }
}
