import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/layout/CommonLayout';
import DefaultErrorPage from 'next/error';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderSteps from 'components/blocks/orderSteps';
import { useRouter } from 'next/router';
import { useStaticUrlGuard } from 'hooks/UseStaticUrlGuard';

const Cart: FC<ServerSidePropsType> = (props) => {
    const router = useRouter();
    const isStaticUrlAllowed = useStaticUrlGuard(router.asPath, props.domainConfig.url);

    if (!isStaticUrlAllowed) {
        return <DefaultErrorPage statusCode={404} />;
    }

    return (
        <CommonLayout>
            <OrderSteps activeStep={1} domainUrl={props.domainConfig.url} />
            Cart - step 1
        </CommonLayout>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default Cart;
