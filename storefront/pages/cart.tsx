import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CartProductList from 'components/Pages/Cart/CartProductList';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderSteps from 'components/Blocks/OrderSteps';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useShopsysSelector } from 'redux/store';

const Cart: FC<ServerSidePropsType> = (props) => {
    const cart = useShopsysSelector((state) => state.user.cart);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={1} domainUrl={props.domainConfig.url} />
                <CartProductList cart={cart} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default Cart;
