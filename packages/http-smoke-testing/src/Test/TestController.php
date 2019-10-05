<?php declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Test;

use Shopsys\HttpSmokeTesting\Annotation\DataSet;
use Shopsys\HttpSmokeTesting\Annotation\Parameter;
use Shopsys\HttpSmokeTesting\Annotation\Skipped;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    /**
     * @param string $name
     * @return Response
     * @Route("/hello/{name}")
     *
     * @DataSet(parameters={
     *     @Parameter("name", value="Batman")
     * })
     * @DataSet(statusCode=404, parameters={
     *     @Parameter("name", value="World")
     * })
     */
    public function helloAction(string $name): Response
    {
        if ($name === 'Batman') {
            return new Response(sprintf('I am %1$s!', $name), 200);
        } else {
            return new Response('Nothing found.', 404);
        }
    }

    /**
     * @return Response
     * @Route("/untested")
     * @Skipped()
     */
    public function untestedAction(): Response
    {
        return new Response('', 500);
    }
}
