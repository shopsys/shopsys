import { CartLoading } from './CartLoading';
import { EmptyCart } from './EmptyCart';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { CurrentCartType } from 'types/cart';

type EmptyCartWrapperProps = {
    currentCart: CurrentCartType;
    title: string;
    isCartPage?: boolean;
    enableHandling?: boolean;
};

export const EmptyCartWrapper: FC<EmptyCartWrapperProps> = ({
    currentCart,
    title,
    children,
    isCartPage = false,
    enableHandling = true,
}) => {
    const router = useRouter();
    const loginLoading = usePersistStore((s) => s.loginLoading);
    const { url } = useDomainConfig();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const [initiatedLoading, setInitiatedLoading] = useState(false);
    const [isLoadingVisible, setIsLoadingVisible] = useState(true);
    const isLoading = !currentCart.isInitiallyLoaded || currentCart.isLoading;
    const isLoginLoading = loginLoading !== 'not-loading';

    useEffect(() => {
        if (enableHandling === false) {
            setIsLoadingVisible(true);
            return;
        }

        if (isLoading || currentCart.isCartEmpty) {
            setInitiatedLoading(true);
        }

        if (initiatedLoading && !isLoading) {
            if (
                currentCart.isCartEmpty === false &&
                router.route === '/order/contact-information' &&
                (currentCart.transport === null || currentCart.payment === null)
            ) {
                router.replace(transportAndPaymentUrl);
            } else {
                setIsLoadingVisible(false);
            }
        }
    }, [
        initiatedLoading,
        isLoading,
        currentCart.payment,
        currentCart.transport,
        currentCart.isCartEmpty,
        router,
        transportAndPaymentUrl,
        enableHandling,
    ]);

    if (isLoadingVisible || isLoginLoading) {
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
