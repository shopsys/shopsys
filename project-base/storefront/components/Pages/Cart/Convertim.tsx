import { getCart, getGtm, getPayments, getStores, getTransports } from './convertimUtils';
import { ConvertimComponent } from 'convertim-react-lib';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useTransportsWithStoresQuery } from 'graphql/requests/transports/queries/TransportsWithStoresQuery.generated';
import useTranslation from 'next-translate/useTranslation';

type ConvertimProps = { cart: TypeCartFragment; convertimUuid: string };

export const Convertim: FC<ConvertimProps> = ({ cart, convertimUuid }) => {
    const { t } = useTranslation();
    const [{ data: transportsData, fetching: isTransportsFetching }] = useTransportsWithStoresQuery({
        variables: { cartUuid: cart.uuid },
        requestPolicy: 'network-only',
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

    if (isTransportsFetching) {
        return null;
    }

    return (
        <ConvertimComponent
            convertimUuid={convertimUuid}
            getCart={getCart(cart)}
            getPayments={getPayments(transportsData?.transports)}
            getStores={getStores(dayNames, cart, transportsData?.transports)}
            getTransports={getTransports(transportsData?.transports)}
            gtm={getGtm()}
            isProduction={false}
            callbacks={{
                afterSaveOrder: (orderObject, continueFunction) => {
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
