<?php

declare(strict_types=1);

namespace App\Model\Cart\Watcher;

use App\Model\Product\Availability\ProductAvailabilityFacade;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade as BaseCartWatcherFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig\Environment;

/**
 * @method checkModifiedPrices(\App\Model\Cart\Cart $cart)
 * @method checkNotListableItems(\App\Model\Cart\Cart $cart)
 * @method checkCartModifications(\App\Model\Cart\Cart $cart)
 */
class CartWatcherFacade extends BaseCartWatcherFacade
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher $cartWatcher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Twig\Environment $twigEnvironment
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        FlashBagInterface $flashBag,
        EntityManagerInterface $em,
        CartWatcher $cartWatcher,
        CurrentCustomerUser $currentCustomerUser,
        Environment $twigEnvironment,
        ProductAvailabilityFacade $productAvailabilityFacade,
        Domain $domain
    ) {
        parent::__construct($flashBag, $em, $cartWatcher, $currentCustomerUser, $twigEnvironment);
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->domain = $domain;
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @property \App\Model\Cart\Watcher\CartWatcher $cartWatcher
     * @return \App\Model\Cart\Item\CartItem[]|null[]
     */
    public function checkUnavailableStockQuantityItems(Cart $cart): array
    {
        $cartItemsToDelete = [];
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $maximumOrderQuantity = $this->productAvailabilityFacade->getMaximumOrderQuantity($product, $this->domain->getId());
            if ($maximumOrderQuantity === 0) {
                $cartItemsToDelete[] = $item;
            }

            if ($maximumOrderQuantity > 0 && $item->getQuantity() > $maximumOrderQuantity) {
                $item->changeQuantity($maximumOrderQuantity);
                $item->changeAddedAt(new DateTime());
                $this->em->persist($item);
                $this->em->flush();

                $messageTemplate = $this->twigEnvironment->createTemplate(
                    t('Množství zboží <strong>{{ name }}</strong> ve Vašem košíku bylo upraveno z důvodu změny dostupnosti. Prosím zkontrolujte si svojí objednávku.')
                );
                $this->flashBag->add(FlashMessage::KEY_INFO, $messageTemplate->render(['name' => $product->getName()]));
            }
        }
        return $cartItemsToDelete;
    }
}
