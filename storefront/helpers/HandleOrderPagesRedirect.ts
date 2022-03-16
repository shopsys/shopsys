import { GetServerSidePropsContext, Redirect } from 'next';
import { CartInput } from 'types/cart';
import { getDomainConfig } from 'utils/Domain/Domain';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

export const handleOrderPagesRedirect = (
    context: GetServerSidePropsContext,
    cartInput: CartInput,
    isCartEmpty: boolean,
): { redirect: Redirect } | false => {
    const domainConfig = getDomainConfig(context.req.headers.host);

    if (isCartEmpty && context.resolvedUrl !== '/cart') {
        const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainConfig.url);

        return {
            redirect: {
                destination: cartUrl,
                permanent: false,
            },
        };
    }

    if (
        context.resolvedUrl !== '/order/transport-and-payment' &&
        (cartInput.transport === null || cartInput.payment === null)
    ) {
        const [transportAndPaymentUrl] = getInternationalizedStaticUrls(
            ['/order/transport-and-payment'],
            domainConfig.url,
        );

        return {
            redirect: {
                destination: transportAndPaymentUrl,
                permanent: false,
            },
        };
    }

    return false;
};
