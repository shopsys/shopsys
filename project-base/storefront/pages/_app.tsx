import 'dayjs/locale/cs';
import 'dayjs/locale/sk';
import 'lightgallery/css/lg-thumbnail.css';
import 'lightgallery/css/lightgallery.css';
import 'react-loading-skeleton/dist/skeleton.css';
import '../styles/globals.css';
import '../styles/user-text.css';
import 'react-toastify/dist/ReactToastify.css';
import 'nprogress/nprogress.css';
import { extend, locale } from 'dayjs';
import appWithI18n from 'next-translate/appWithI18n';
import LocalizedFormat from 'dayjs/plugin/localizedFormat';
import { ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import i18nConfig from 'i18n';
import { AppProps as NextAppProps } from 'next/app';
import { ReactElement, useMemo } from 'react';
import { AppPageContent } from 'components/Pages/App/AppPageContent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { createClient } from 'helpers/urql/createClient';
import { Provider, ssrExchange } from 'urql';

extend(LocalizedFormat);

type ErrorProps = {
    err?: any;
};

type AppProps = {
    pageProps: ServerSidePropsType;
} & Omit<NextAppProps<ErrorProps>, 'pageProps'> &
    ErrorProps;

function MyApp({ Component, pageProps, err }: AppProps): ReactElement | null {
    const { defaultLocale, publicGraphqlEndpoint } = pageProps.domainConfig;
    locale(defaultLocale);
    const t = useTypedTranslationFunction();

    const urqlClient = useMemo(
        () => createClient(t, ssrExchange({ initialState: pageProps.urqlState }), publicGraphqlEndpoint),
        [publicGraphqlEndpoint, pageProps.urqlState, t],
    );

    if (urqlClient === null) {
        return null;
    }

    return (
        <Provider value={urqlClient}>
            <AppPageContent Component={Component} pageProps={pageProps} err={err} />
        </Provider>
    );
}

// eslint-disable-next-line
// @ts-ignore
export default appWithI18n(MyApp, { ...i18nConfig });
