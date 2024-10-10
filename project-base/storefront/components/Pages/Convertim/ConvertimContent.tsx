import { getCart, getGtm, getPayments, getStores, getTransports } from './convertimUtils';
import { Webline } from 'components/Layout/Webline/Webline';
import { ConvertimComponent } from 'convertim-react-lib';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useTransportsWithStoresQuery } from 'graphql/requests/transports/queries/TransportsWithStoresQuery.generated';
import useTranslation from 'next-translate/useTranslation';

type CartContentProps = { cart: TypeCartFragment };

export const ConvertimContent: FC<CartContentProps> = ({ cart }) => {
    const { t } = useTranslation();
    const [{ data: transportsData }] = useTransportsWithStoresQuery({
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

    return (
        <Webline>
            <div className="flex h-96 items-center justify-center bg-backgroundMore text-inputText">Placeholder</div>
            <ConvertimComponent
                convertimUuid="CONVERTIM-PROJECT-ID"
                getCart={getCart(cart)}
                getPayments={getPayments(transportsData?.transports)}
                getStores={getStores(dayNames, cart, transportsData?.transports)}
                getTransports={getTransports(transportsData?.transports)}
                gtm={getGtm()}
                isProduction={false}
                callbacks={{
                    afterLogout: () => {},
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
        </Webline>
    );
};
