import { MinimalCartQueryApi, MinimalCartQueryDocumentApi, MinimalCartQueryVariablesApi } from 'graphql/generated';
import { getDomainConfig } from 'helpers/domain/domain';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { GetServerSidePropsContext, Redirect } from 'next';
import { AppStore } from 'redux/main';
import { Client } from 'urql';

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

    const minimalCartQueryResult = await client
        .query<MinimalCartQueryApi, MinimalCartQueryVariablesApi>(MinimalCartQueryDocumentApi, { cartUuid })
        .toPromise();

    const isCartEmpty =
        minimalCartQueryResult.data?.cart?.items === undefined || minimalCartQueryResult.data.cart.items.length === 0;
    const transport = minimalCartQueryResult.data?.cart?.transport ?? null;
    const payment = minimalCartQueryResult.data?.cart?.payment ?? null;

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
