import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { Provider, ssrExchange } from 'urql';
import { createClient } from 'urql/createClient';
import { ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

export const UrqlWrapper: FC<{ pageProps: ServerSidePropsType }> = ({ children, pageProps }) => {
    const { publicGraphqlEndpoint } = pageProps.domainConfig;
    const { t } = useTranslation();

    const urqlClient = useMemo(
        () =>
            createClient({ t, ssrExchange: ssrExchange({ initialState: pageProps.urqlState }), publicGraphqlEndpoint }),
        [publicGraphqlEndpoint, pageProps.urqlState, t],
    );

    return <Provider value={urqlClient}>{children}</Provider>;
};
