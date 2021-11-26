import { GetServerSidePropsContext, Redirect } from 'next';
import { getCartInputCookie } from './Cookies';
import { getDomainConfig } from 'utils/Domain/Domain';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';

export const handleOrderPagesRedirect = (context: GetServerSidePropsContext): { redirect: Redirect } | false => {
    const domainConfig = getDomainConfig(context.req.headers.host);
    const cartInputCookie = getCartInputCookie(context);

    if (cartInputCookie.isCartEmpty && context.resolvedUrl !== '/cart') {
        const [cartUrl] = useGetInternationalizedStaticUrls(['/cart'], domainConfig.url);

        return {
            redirect: {
                destination: cartUrl,
                permanent: false,
            },
        };
    }

    if (
        context.resolvedUrl !== '/order/transport-and-payment' &&
        (cartInputCookie.transport === null || cartInputCookie.payment === null)
    ) {
        const [transportAndPaymentUrl] = useGetInternationalizedStaticUrls(
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
