<?php

namespace Shopsys\FrameworkBundle\Model\Script;

interface ScriptDataFactoryInterface
{
    public function create(): ScriptData;

    public function createFromScript(Script $script): ScriptData;
}
