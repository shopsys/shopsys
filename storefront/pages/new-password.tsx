import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { NavigationQueryDocumentApi, NotificationBarsDocumentApi } from 'graphql/generated';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import NewPassword from 'components/Pages/NewPassword';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useRouter } from 'next/router';

const Index: FC<ServerSidePropsType> = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);

    const router = useRouter();
    const { hash, email } = router.query;

    let hashParam = '';
    if (hash !== undefined) {
        if (Array.isArray(hash)) {
            hashParam = hash[0];
        } else if (hash.trim() !== '') {
            hashParam = hash.trim();
        }
    }

    let emailParam = '';
    if (email !== undefined) {
        if (Array.isArray(email)) {
            emailParam = email[0];
        } else if (email.trim() !== '') {
            emailParam = email.trim();
        }
    }

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                <NewPassword hash={hashParam} email={emailParam} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [
        { query: NotificationBarsDocumentApi },
        { query: NavigationQueryDocumentApi },
    ]);
});

export default Index;
