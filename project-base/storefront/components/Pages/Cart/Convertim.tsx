import { getGtm, mapCartData, mapPaymentsData, mapStoresData, mapTransportsData } from './convertimUtils';
import {
    ConvertimComponent,
    ConvertimOrderObject,
    GetCartType,
    GetPaymentsType,
    GetStoresType,
    GetTransportsType,
} from 'convertim-react-lib';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useTransportsWithStoresQuery } from 'graphql/requests/transports/queries/TransportsWithStoresQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type ConvertimProps = { cart: TypeCartFragment; convertimUuid: string };

export const Convertim: FC<ConvertimProps> = ({ cart, convertimUuid }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const [{ data: transportsData, fetching: isTransportsFetching }] = useTransportsWithStoresQuery({
        variables: { cartUuid: cart.uuid },
    });

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

    if (isTransportsFetching) {
        return null;
    }

    return (
        <ConvertimComponent
            convertimUuid={convertimUuid}
            getCart={getCart}
            getPayments={getPayments}
            getStores={getStores}
            getTransports={getTransports}
            gtm={getGtm()}
            isProduction={false}
            callbacks={{
                afterSaveOrder: (orderObject: ConvertimOrderObject, continueFunction) => {
                    continueFunction();
                },
                beforeOpenConvertim: (continueFunction) => {
                    continueFunction();
                },
                validateCustomZipTransport: (transportId: string, postalCode: string, setResult: () => void) => {
                    setResult();
                },
            }}
        />
    );
};
