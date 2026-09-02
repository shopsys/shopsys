<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class SecurityHeadersResponseListener
{
    public function __construct(
        protected readonly Setting $setting,
        protected readonly ContextResolverInterface $contextResolver,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
    }

    #[AsEventListener]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->contextResolver->isCurrentContext(FrontendApiContext::class)) {
            return;
        }

        $cspHeader = $this->setting->get(Setting::CSP_HEADER);

        if ($this->parameterBag->get('kernel.environment') === EnvironmentType::DEVELOPMENT) {
            $cspHeader = $this->appendSourcesToDirectives($cspHeader, $this->getDevelopmentCspAppendices());
        }

        $event->getResponse()->headers->set('Content-Security-Policy', $cspHeader);
    }

    /**
     * @return array<string, array<string>>
     */
    protected function getDevelopmentCspAppendices(): array
    {
        return [
            'default-src' => [
                'http://cdnjs.cloudflare.com', // elFinder uses protocol-relative URLs that might resolve to HTTP in the development environment
            ],
            'script-src' => [
                'http://localhost:35729', // Rsbuild's dev server for hot module replacement
                'http://cdnjs.cloudflare.com', // elFinder uses protocol-relative URLs that might resolve to HTTP in the development environment
            ],
            'style-src' => [
                'http://localhost:35729', // Rsbuild's dev server serves styles when hot module replacement is enabled
            ],
            'connect-src' => [
                'ws://localhost:35729', // Rsbuild's dev server hot module replacement websocket
                'http://localhost:35729',
            ],
        ];
    }

    protected function appendSourceToDirective(string $cspHeader, string $directiveName, string $source): string
    {
        $directives = explode(';', $cspHeader);

        foreach ($directives as $index => $directive) {
            $trimmedDirective = trim($directive);

            if (!str_starts_with($trimmedDirective, $directiveName . ' ')) {
                continue;
            }

            if (!str_contains($trimmedDirective, $source)) {
                $directives[$index] = $trimmedDirective . ' ' . $source;
            } else {
                $directives[$index] = $trimmedDirective;
            }

            return implode('; ', array_map('trim', $directives));
        }

        return $cspHeader;
    }

    /**
     * @param array<string, array<string>> $cspAppendicesByDirective
     */
    protected function appendSourcesToDirectives(string $cspHeader, array $cspAppendicesByDirective): string
    {
        foreach ($cspAppendicesByDirective as $directiveName => $sources) {
            foreach ($sources as $source) {
                $cspHeader = $this->appendSourceToDirective($cspHeader, $directiveName, $source);
            }
        }

        return $cspHeader;
    }
}
