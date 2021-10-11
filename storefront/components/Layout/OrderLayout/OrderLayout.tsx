import { OrderLayoutContentStyled, OrderLayoutStyled, OrderLayoutSummaryStyled } from './OrderLayout.style';
import { FC } from 'react';
import Header from 'components/Layout/Header';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import OrderSummary from 'components/Blocks/OrderSummary';
import { useFormContext } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type OrderLayoutProps = {
    activeStep: number;
    buttonNextText: string;
};

/**
 * Page layout for order pages
 */
const OrderLayout: FC<OrderLayoutProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { url } = useShopsysSelector((state) => state.domain);
    const formProviderMethods = useFormContext();

    return (
        <>
            <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
                <Header />
            </Webline>
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
            <Webline>
                <OrderAction
                    activeStep={props.activeStep}
                    buttonBack={t('Back')}
                    buttonNext={props.buttonNextText}
                    isDisabled={!formProviderMethods.formState.isValid}
                    withGapTop={true}
                    withGapBottom={true}
                />
            </Webline>
        </>
    );
};

/* @component */
export default OrderLayout;
