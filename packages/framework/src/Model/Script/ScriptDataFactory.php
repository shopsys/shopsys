<?php

namespace Shopsys\FrameworkBundle\Model\Script;

class ScriptDataFactory implements ScriptDataFactoryInterface
{
    public function create(): ScriptData
    {
        return new ScriptData();
    }

    public function createFromScript(Script $script): ScriptData
    {
        $scriptData = new ScriptData();
        $this->fillFromScript($scriptData, $script);

        return $scriptData;
    }

    protected function fillFromScript(ScriptData $scriptData, Script $script): void
    {
        $scriptData->name = $script->getName();
        $scriptData->code = $script->getCode();
        $scriptData->placement = $script->getPlacement();
    }
}
