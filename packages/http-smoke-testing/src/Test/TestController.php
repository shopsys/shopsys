<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Test;

use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Parameter;
use Shopsys\HttpSmokeTesting\Annotation\Skipped;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController
{
    /**
     * @DataSet(parameters={
     *     @Parameter(name="name", value="Batman")
     * })
     * @DataSet(statusCode=404, parameters={
     *     @Parameter(name="name", value="World")
     * })
     */
    #[Route(path: '/hello/{name}')]
    public function helloAction(string $name): Response
    {
        if ($name === 'Batman') {
            return new Response(sprintf('I am %1$s!', $name), 200);
        }

        return new Response('Nothing found.', 404);
    }

    /**
     * @DataSet(parameters={
     *     @Parameter(name="myName", value="Batman")
     * })
     */
    #[Route(path: '/test')]
    public function testAction(string $name): Response
    {
        if ($name === 'Batman') {
            return new Response(sprintf('I am %1$s!', $name), 200);
        }

        return new Response('Nothing found.', 404);
    }

    /**
     * @Skipped()
     */
    #[Route(path: '/untested')]
    public function untestedAction(): Response
    {
        return new Response('', 500);
    }
}
