import { useMemo } from 'react';
import { ssrExchange as createSsrExchange, Provider, SSRExchange } from 'urql';
import { createClient } from 'urql/createClient';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isClient } from 'utils/isClient';
import { ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

let ssrExchange: SSRExchange | null = null;

export const UrqlWrapper: FC<{ pageProps: ServerSidePropsType }> = ({ children, pageProps }) => {
    const { t } = useTranslation();

    // Keep useMemo - has side effects (mutates module-level ssrExchange, critical for SSR hydration)
    const client = useMemo(() => {
        if (!ssrExchange || typeof window === 'undefined') {
            ssrExchange = createSsrExchange({
                initialState: pageProps.urqlState,
                isClient: isClient,
            });
        } else {
            ssrExchange.restoreData(pageProps.urqlState);
        }

        return createClient({ t, ssrExchange, domainConfig: pageProps.domainConfig });
    }, [pageProps.urqlState]);

    return <Provider value={client}>{children}</Provider>;
};
