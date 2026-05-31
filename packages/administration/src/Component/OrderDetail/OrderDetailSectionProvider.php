<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

use Override;

class OrderDetailSectionProvider implements OrderDetailSectionProviderInterface
{
    /**
     * @return iterable<\Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSection>
     */
    #[Override]
    public function getSections(): iterable
    {
        yield new OrderDetailSection(
            'tracking',
            '@ShopsysAdministration/content/order/detail/sections/payment_transport_view.html.twig',
            '@ShopsysAdministration/content/order/detail/sections/payment_transport_form.html.twig',
            t('Edit tracking'),
            t('Tracking saved.'),
        );

        yield new OrderDetailSection(
            'note',
            '@ShopsysAdministration/content/order/detail/sections/note_view.html.twig',
            '@ShopsysAdministration/content/order/detail/sections/note_form.html.twig',
            t('Edit note'),
            t('Note saved.'),
        );

        yield new OrderDetailSection(
            'personal',
            '@ShopsysAdministration/content/order/detail/sections/personal_view.html.twig',
            '@ShopsysAdministration/content/order/detail/sections/personal_form.html.twig',
            t('Edit personal data'),
            t('Personal data saved.'),
        );

        yield new OrderDetailSection(
            'billingAddress',
            '@ShopsysAdministration/content/order/detail/sections/billing_address_view.html.twig',
            '@ShopsysAdministration/content/order/detail/sections/billing_address_form.html.twig',
            t('Edit billing details'),
            t('Billing details saved.'),
        );

        yield new OrderDetailSection(
            'deliveryAddress',
            '@ShopsysAdministration/content/order/detail/sections/delivery_address_view.html.twig',
            '@ShopsysAdministration/content/order/detail/sections/delivery_address_form.html.twig',
            t('Edit delivery address'),
            t('Delivery address saved.'),
        );

        yield new OrderDetailSection(
            'withdrawal',
            '@ShopsysAdministration/content/order/detail/sections/withdrawal_view.html.twig',
            '@ShopsysAdministration/content/order/detail/sections/withdrawal_form.html.twig',
            t('Edit withdrawal request'),
            t('Withdrawal request saved.'),
        );
    }
}
