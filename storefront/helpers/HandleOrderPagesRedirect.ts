import { getCartInputCookie } from './Cookies';
import { getDomainConfig } from 'utils/Domain/Domain';
import { GetServerSidePropsContext } from 'next';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';

export const handleOrderPagesRedirect = (
    context: GetServerSidePropsContext,
):
    | {
          redirect: {
              destination: string;
              permanent: false;
          };
      }
    | false => {
    const domainUrl = getDomainConfig(context.req.headers.host)?.url;
    const cartInputCookie = getCartInputCookie(context);

    if (
        context.resolvedUrl !== '/order/transport-and-payment' &&
        (cartInputCookie.transport === null || cartInputCookie.payment === null)
    ) {
        const [transportAndPaymentUrl] = useGetInternationalizedStaticUrls(['/order/transport-and-payment'], domainUrl);

        return {
            redirect: {
                destination: transportAndPaymentUrl,
                permanent: false,
            },
        };
    }

    if (cartInputCookie.cartUuid === null && context.resolvedUrl !== '/cart') {
        const [cartUrl] = useGetInternationalizedStaticUrls(['/cart'], domainUrl);

        return {
            redirect: {
                destination: cartUrl,
                permanent: false,
            },
        };
    }

    return false;
};
