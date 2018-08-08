<?php

namespace Shopsys\FrameworkBundle\Model\Script;

interface ScriptFactoryInterface
{
    public function create(ScriptData $data): Script;
}
