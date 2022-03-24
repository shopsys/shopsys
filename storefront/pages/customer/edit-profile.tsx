import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

import CommonLayout from 'components/Layout/CommonLayout';
import EditProfile from 'components/Pages/Customer/EditProfile';
import { FC } from 'react';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import PageGuard from 'components/Helpers/PageGuard';
import SimpleLayout from 'components/Layout/SimpleLayout';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const EditProfilePage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);
    const [customerUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/edit-profile'],
        domainUrl,
    );
    const currentCustomerUserData = useCurrentCustomerData();

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <PageGuard accessCondition={isUserLoggedIn} errorRedirectUrl="/">
                <CommonLayout>
                    <SimpleLayout
                        heading={t('Edit profile')}
                        breadcrumb={[
                            { name: t('Customer'), slug: customerUrl },
                            { name: t('Edit profile'), slug: customerEditProfileUrl },
                        ]}
                    >
                        {currentCustomerUserData !== undefined && (
                            <EditProfile defaultFormValues={currentCustomerUserData} />
                        )}
                    </SimpleLayout>
                </CommonLayout>
            </PageGuard>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default EditProfilePage;
