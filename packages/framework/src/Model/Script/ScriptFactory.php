<?php

namespace Shopsys\FrameworkBundle\Model\Script;

class ScriptFactory implements ScriptFactoryInterface
{

    public function create(ScriptData $data): Script
    {
        return new Script($data);
    }
}
