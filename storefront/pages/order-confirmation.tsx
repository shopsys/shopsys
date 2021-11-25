import { FC, useEffect } from 'react';
import CommonLayout from 'components/Layout/CommonLayout';
import OrderConfirmation from 'components/Pages/OrderConfirmation';
import Registration from 'components/Pages/OrderConfirmation/Registration';
import router from 'next/router';
import { ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useInitDomainConfigOnClient } from 'helpers/InitDomainConfig';
import { useShopsysSelector } from 'redux/main';

const Index: FC<ServerSidePropsType> = () => {
    useInitDomainConfigOnClient();
    const { canAccessOrderConfirmation } = useShopsysSelector((state) => state.user);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl] = useGetInternationalizedStaticUrls(['/cart'], domainUrl);
    useEffect(() => {
        if (!canAccessOrderConfirmation) {
            router.replace(cartUrl);
        }
    }, []);

    if (canAccessOrderConfirmation) {
        return (
            <CommonLayout>
                <OrderConfirmation />
                <Registration />
            </CommonLayout>
        );
    }

    return null;
};

export default Index;
