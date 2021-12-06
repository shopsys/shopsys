import { GetServerSidePropsContext, Redirect } from 'next';
import { CartInput } from 'types/cart';
import { getDomainConfig } from 'utils/Domain/Domain';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';

export const handleOrderPagesRedirect = (
    context: GetServerSidePropsContext,
    cartInput: CartInput,
): { redirect: Redirect } | false => {
    const domainConfig = getDomainConfig(context.req.headers.host);

    if (cartInput.isCartEmpty && context.resolvedUrl !== '/cart') {
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
        (cartInput.transport === null || cartInput.payment === null)
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
