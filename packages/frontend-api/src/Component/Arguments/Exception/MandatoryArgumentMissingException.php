<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Arguments\Exception;

use Exception;

class MandatoryArgumentMissingException extends Exception implements BuilderArgumentException
{
}
