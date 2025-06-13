<?php

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ControllerClass extends AbstractController
{
    // Extends different class, should not be changed
    public function indexAction(): Response
    {
        return new Response();
    }
}
