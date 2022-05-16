import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import NewPassword from 'components/Pages/NewPassword';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

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
    return initServerSideProps(context, store);
});

export default Index;
