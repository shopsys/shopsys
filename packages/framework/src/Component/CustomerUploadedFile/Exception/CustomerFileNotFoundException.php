<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception;

use App\Environment;
use Exception;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerFileNotFoundException extends CustomerFileException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        $isDev = Environment::getEnvironment() === EnvironmentType::DEVELOPMENT;

        if (!$isDev) {
            throw new NotFoundHttpException($message, $previous, 0, ['X-Accel-Redirect' => '@storefront']);
        }

        parent::__construct($message, $previous, 404);
    }
}
