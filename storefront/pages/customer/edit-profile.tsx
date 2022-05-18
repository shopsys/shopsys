import PageGuard from 'components/Helpers/PageGuard';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import SimpleLayout from 'components/Layout/SimpleLayout';
import EditProfile from 'components/Pages/Customer/EditProfile';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

const EditProfilePage: FC = () => {
    const t = useTypedTranslationFunction();
    const { isUserLoggedIn } = useCurrentUserData();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
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
                            <EditProfile currentCustomerUser={currentCustomerUserData} />
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
