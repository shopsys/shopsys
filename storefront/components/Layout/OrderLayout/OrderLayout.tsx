import { OrderLayoutContentStyled, OrderLayoutStyled, OrderLayoutSummaryStyled } from './OrderLayout.style';
import Adverts from 'components/Blocks/Adverts';
import OrderSteps from 'components/Blocks/OrderSteps';
import OrderSummary from 'components/Blocks/OrderSummary';
import Header from 'components/Layout/Header';
import NotificationBars from 'components/Layout/NotificationBars';
import Webline from 'components/Layout/Webline';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

type OrderLayoutProps = {
    activeStep: number;
    buttonNextText: string;
};

/**
 * Page layout for order pages
 */
const OrderLayout: FC<OrderLayoutProps> = (props) => {
    const { url } = useShopsysSelector((state) => state.domain);

    return (
        <>
            <NotificationBars />
            <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
                <Header simpleHeader />
            </Webline>
            <Adverts positionName="header" withGapBottom withWebline />
            <Webline>
                <OrderSteps activeStep={props.activeStep} domainUrl={url} />
            </Webline>
            <Webline>
                <OrderLayoutStyled>
                    <OrderLayoutContentStyled>{props.children}</OrderLayoutContentStyled>
                    <OrderLayoutSummaryStyled>
                        <OrderSummary />
                    </OrderLayoutSummaryStyled>
                </OrderLayoutStyled>
            </Webline>
            <Adverts positionName="footer" withGapBottom withWebline />
        </>
    );
};

/* @component */
export default OrderLayout;
