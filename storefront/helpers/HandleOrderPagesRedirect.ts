import { CartQueryApi, CartQueryDocumentApi, CartQueryVariablesApi } from 'graphql/generated';
import { GetServerSidePropsContext, Redirect } from 'next';
import { AppStore } from 'redux/main';
import { Client } from 'urql';
import { getDomainConfig } from 'utils/Domain/Domain';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

export const handleOrderPagesRedirect = async (
    context: GetServerSidePropsContext,
    store: AppStore,
    client: Client | null,
): Promise<{ redirect: Redirect } | false> => {
    const domainConfig = getDomainConfig(context.req.headers.host);
    const [cartUrl, transportAndPaymentUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment'],
        domainConfig.url,
    );
    const cartUuid = store.getState().user.cartUuid;

    if (client === null) {
        return {
            redirect: {
                destination: cartUrl,
                permanent: false,
            },
        };
    }

    const cartQueryResult = await client
        .query<CartQueryApi, CartQueryVariablesApi>(CartQueryDocumentApi, { cartUuid })
        .toPromise();

    const isCartEmpty = cartQueryResult.data?.cart?.items === undefined || cartQueryResult.data.cart.items.length === 0;
    const transport = cartQueryResult.data?.cart?.transport ?? null;
    const payment = cartQueryResult.data?.cart?.payment ?? null;

    if (isCartEmpty && context.resolvedUrl !== '/cart') {
        return {
            redirect: {
                destination: cartUrl,
                permanent: false,
            },
        };
    }

    if (context.resolvedUrl !== '/order/transport-and-payment' && (transport === null || payment === null)) {
        return {
            redirect: {
                destination: transportAndPaymentUrl,
                permanent: false,
            },
        };
    }

    return false;
};
