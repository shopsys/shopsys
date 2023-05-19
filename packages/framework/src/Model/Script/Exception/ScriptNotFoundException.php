<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Script\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScriptNotFoundException extends NotFoundHttpException implements ScriptException
{
}
