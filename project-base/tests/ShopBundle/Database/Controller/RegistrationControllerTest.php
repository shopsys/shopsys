<?php

namespace Tests\ShopBundle\Database\Controller;

use Shopsys\FrameworkBundle\Component\Form\FormTimeProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\ShopBundle\Test\DatabaseTestCase;

class RegistrationControllerTest extends DatabaseTestCase
{
    public function testSuccessfulRegistration()
    {
        $client = $this->getClient();

        $container = $client->getContainer();
        $this->registerAlwaysValidFormTimeProvider($container);

        $tokenManager = $container->get('security.csrf.token_manager');
        $token = $tokenManager->getToken('registration_form');

        $parameters = [
            'registration_form' => [
                '_token' => $token,
                'firstName' => 'Aella',
                'lastName' => 'Minos',
                'email' => 'no-reply.registration-test@netdevelo.cz',
                'password' => [
                    'first' => '123456',
                    'second' => '123456',
                ],
                'privacyPolicy' => true,
            ],
        ];
        $client->request('POST', '/registration/', $parameters);
        $code = $client->getResponse()->getStatusCode();

        $this->assertSame(302, $code);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function registerAlwaysValidFormTimeProvider(ContainerInterface $container): void
    {
        $session = $container->get('session');
        $container->set('autowired.' . FormTimeProvider::class, new AlwaysValidFormTimeProvider($session));
    }
}
