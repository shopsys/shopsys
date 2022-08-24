import { OrderLayoutContentStyled, OrderLayoutStyled, OrderLayoutSummaryStyled } from './OrderLayout.style';
import { SeoMeta } from 'components/Basic/Head/SeoMeta/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { OrderSteps } from 'components/Blocks/OrderSteps/OrderSteps';
import { OrderSummary } from 'components/Blocks/OrderSummary/OrderSummary';
import { Header } from 'components/Layout/Header/Header';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

type OrderLayoutProps = {
    activeStep: number;
    buttonNextText: string;
};

export const OrderLayout: FC<OrderLayoutProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { url } = useShopsysSelector((state) => state.domain);

    return (
        <>
            <SeoMeta title={t('Order')} />
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
