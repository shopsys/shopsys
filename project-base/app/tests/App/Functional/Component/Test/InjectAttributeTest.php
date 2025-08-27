<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Test;

use App\Component\Test\Inject;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * Test to verify that the #[Inject] attribute works alongside @inject annotation
 */
class InjectAttributeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AdministratorFrontSecurityFacade $administratorFrontSecurityFacadeUsingAnnotation;

    /**
     * Modern #[Inject] attribute - new functionality
     */
    #[Inject]
    private Domain $domainUsingAttribute;

    /**
     * #[Inject] with explicit service ID using class name
     */
    #[Inject(Domain::class)]
    private Domain $domainUsingAttributeWithExplicitServiceId;

    public function testInjectAnnotationStillWorks(): void
    {
        // Test that the traditional @inject annotation still works
        $this->assertInstanceOf(AdministratorFrontSecurityFacade::class, $this->administratorFrontSecurityFacadeUsingAnnotation);
        $this->assertFalse($this->administratorFrontSecurityFacadeUsingAnnotation->isAdministratorLogged());
    }

    public function testInjectAttributeWorks(): void
    {
        // Test that the new #[Inject] attribute works
        $this->assertInstanceOf(Domain::class, $this->domainUsingAttribute);
        $this->assertSame(Domain::FIRST_DOMAIN_ID, $this->domainUsingAttribute->getId());
    }

    public function testInjectAttributeWithExplicitServiceIdWorks(): void
    {
        // Test that the #[Inject] attribute works with explicit service ID
        $this->assertInstanceOf(Domain::class, $this->domainUsingAttributeWithExplicitServiceId);
        $this->assertSame(Domain::FIRST_DOMAIN_ID, $this->domainUsingAttributeWithExplicitServiceId->getId());
        
        // Both should be the same instance (singleton)
        $this->assertSame($this->domainUsingAttribute, $this->domainUsingAttributeWithExplicitServiceId);
    }
}