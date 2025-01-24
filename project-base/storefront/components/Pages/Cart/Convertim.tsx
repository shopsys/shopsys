import { getGtm, mapCartData, mapPaymentsData, mapStoresData, mapTransportsData } from './convertimUtils';
import {
    ConvertimComponent,
    GetCartType,
    GetPaymentsType,
    GetStoresType,
    GetTransportsType,
} from 'convertim-react-lib';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useRemoveCartMutation } from 'graphql/requests/cart/mutations/RemoveCartMutation.generated';
import { useTransportsWithPaymentsAndStoresForConvertimQuery } from 'graphql/requests/transports/queries/TransportsWithPaymentsAndStoresForConvertimQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useLogout } from 'utils/auth/useLogout';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type ConvertimProps = { cart?: TypeCartFragment | null; convertimProjectUuid: string };

export const Convertim: FC<ConvertimProps> = ({ cart, convertimProjectUuid }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const [, removeCartMutation] = useRemoveCartMutation();
    const isUserLoggedIn = useIsUserLoggedIn();
    const [{ data: transportsData, fetching: isTransportsFetching }] =
        useTransportsWithPaymentsAndStoresForConvertimQuery({
            variables: { cartUuid: cart?.uuid ?? null, displayInCartOnly: false },
        });
    const logout = useLogout();

    const dayNames = [
        t('Monday'),
        t('Tuesday'),
        t('Wednesday'),
        t('Thursday'),
        t('Friday'),
        t('Saturday'),
        t('Sunday'),
    ];

    const getCart = useCallback<GetCartType>((setData) => setData(mapCartData(cart, formatPrice)), [cart, formatPrice]);
    const getPayments = useCallback<GetPaymentsType>(
        (setData) => setData(mapPaymentsData(transportsData?.transports)),
        [transportsData],
    );
    const getStores = useCallback<GetStoresType>(
        (setData) => setData(mapStoresData(dayNames, cart, transportsData?.transports)),
        [dayNames, cart, transportsData],
    );
    const getTransports = useCallback<GetTransportsType>(
        (setData) => setData(mapTransportsData(transportsData?.transports, t)),
        [transportsData],
    );

    const handleEventsAfterOrderCreation = async () => {
        if (!cart || (!cart.uuid && !isUserLoggedIn)) {
            // skip cart removal
            return;
        }
        await removeCartMutation({ cartUuid: isUserLoggedIn ? null : cart.uuid });
        updateCartUuid(null);
    };

    if (isTransportsFetching) {
        return null;
    }

    return (
        <ConvertimComponent
            convertimUuid={convertimProjectUuid}
            getCart={getCart}
            getPayments={getPayments}
            getStores={getStores}
            getTransports={getTransports}
            gtm={getGtm()}
            isProduction={false}
            callbacks={{
                afterSaveOrder: (_, continueFunction) => {
                    handleEventsAfterOrderCreation().then(() => continueFunction());
                },
                beforeOpenConvertim: (continueFunction) => {
                    continueFunction();
                },
                validateCustomZipTransport: (_, __, setResult: () => void) => {
                    setResult();
                },
                afterLogout: () => {
                    logout();
                },
            }}
        />
    );
};
