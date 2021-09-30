import 'react-toastify/dist/ReactToastify.css';
import { cartQuery, mapCart } from 'connectors/cart/Cart';
import { ReactElement, useEffect } from 'react';
import { useShopsysDispatch, useShopsysSelector, wrapper } from 'redux/store';
import { AppProps } from 'next/app';
import { appWithTranslation } from 'next-i18next';
import { getDomainConfig } from 'utils/Domain/Domain';
import { getUserDataCookie } from 'helpers/Cookies';
import nextI18NextConfig from 'next-i18next.config';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { ToastContainer } from 'react-toastify';
import { useQuery } from 'urql';
import { userActions } from 'redux/store/UserStore';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { withUrqlClient } from 'next-urql';

function MyApp({ Component, pageProps }: AppProps): ReactElement {
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const userData = getUserDataCookie();
    const [result] = useQuery({
        query: cartQuery,
        variables: {
            cartUuid: userData.cartUuid,
        },
        pause: userData.cartUuid === undefined,
    });
    useEffect(() => {
        if (result.error) {
            showErrorMessage(t('Hooops, someting wrong happend.'));
        } else if (result.data !== undefined) {
            dispatch(userActions.setCart(mapCart(result.data.cart, currencyCode)));
        }
    }, [result]);

    return (
        <ShopsysGlobalProvider>
            <ToastContainer autoClose={6000} position="top-center" theme="colored" />
            <Component {...pageProps} />
        </ShopsysGlobalProvider>
    );
}

/**
 * We need to define "something" on the server side, even though it is not used at all.
 * On the server side, the URL is actually defined in initUrqlClient in InitServerSideProps.
 */
const getApiUrl = () => {
    let apiUrl = 'defaultUrl';
    if (typeof window !== 'undefined') {
        apiUrl = getDomainConfig(window.location.host).publicGraphqlEndpoint;
    }
    return apiUrl;
};

export default wrapper.withRedux(
    withUrqlClient(
        () => ({
            url: getApiUrl(),
        }),
        { ssr: false },
    )(
        // eslint-disable-next-line
        // @ts-ignore
        appWithTranslation(MyApp, nextI18NextConfig),
    ),
);
