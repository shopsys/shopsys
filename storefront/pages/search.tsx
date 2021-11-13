import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import { nextReduxWrapper } from 'redux/main';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
const Search: FC<ServerSidePropsType> = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                <SearchPage />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);

    return initServerSideProps(context, store, [
        { query: NavigationQueryDocumentApi },
    ]);
});

export default Search;
