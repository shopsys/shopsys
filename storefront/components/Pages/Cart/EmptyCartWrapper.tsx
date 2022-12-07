import { CartLoading } from './CartLoading/CartLoading';
import { EmptyCart } from './EmptyCart/EmptyCart';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { FC, useEffect, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { CurrentCartType } from 'types/cart';

type EmptyCartWrapperProps = {
    currentCart: CurrentCartType;
    title: string;
    isCartPage?: boolean;
};

export const EmptyCartWrapper: FC<EmptyCartWrapperProps> = ({ currentCart, title, children, isCartPage = false }) => {
    const router = useRouter();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], domainUrl);
    const [initiatedLoading, setInitiatedLoading] = useState(false);
    const [isLoadingVisible, setIsLoadingVisible] = useState(true);
    const isLoading = !currentCart.isInitiallyLoaded || currentCart.isLoading;

    useEffect(() => {
        if (isLoading) {
            setInitiatedLoading(true);
        }

        if (initiatedLoading && !isLoading) {
            if (
                (currentCart.transport === null || currentCart.payment === null) &&
                router.route === '/order/contact-information'
            ) {
                router.replace(transportAndPaymentUrl);
            } else {
                setIsLoadingVisible(false);
            }
        }
    }, [initiatedLoading, isLoading, currentCart.payment, currentCart.transport, router, transportAndPaymentUrl]);

    if (isLoadingVisible) {
        return isCartPage ? (
            <CommonLayout title={title}>
                <CartLoading />
            </CommonLayout>
        ) : (
            <OrderLayout activeStep={2}>
                <CartLoading />
            </OrderLayout>
        );
    }

    return currentCart.isCartEmpty ? (
        <CommonLayout title={title}>
            <EmptyCart />
        </CommonLayout>
    ) : (
        <>{children}</>
    );
};
