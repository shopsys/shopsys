<?php

declare(strict_types=1);

namespace Shopsys\Cli\Model;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TwigHandler
{
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader();
        $this->twig = new Environment($loader, [
            'autoescape' => false,
            'strict_variables' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function render(string $templatePath, array $variables = []): string
    {
        $templateDir = dirname($templatePath);
        $templateName = basename($templatePath);

        $loader = $this->twig->getLoader();

        if ($loader instanceof FilesystemLoader) {
            $loader->addPath($templateDir);
        }

        return $this->twig->render($templateName, $variables);
    }
}
