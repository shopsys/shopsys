import { OrderStepsListItemLinkStyled, OrderStepsListItemStyled, OrderStepsListStyled } from './OrderSteps.style';
import { FC } from 'react';
import Link from 'next/link';
import { useTranslation } from 'next-i18next';
import Webline from 'components/layout/Webline';

type OrderStepsProps = {
    activeStep: number;
};

const OrderSteps: FC<OrderStepsProps> = (props) => {
    const { t } = useTranslation();

    return (
        <Webline>
            <OrderStepsListStyled>
                <OrderStepsListItemStyled>
                    <Link href="/cart" passHref>
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 1}>
                            {'1. ' + t('Cart')}
                        </OrderStepsListItemLinkStyled>
                    </Link>
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled>
                    <Link href="/order/shipment-and-payment" passHref>
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 2}>
                            {'2. ' + t('Shipment and payment')}
                        </OrderStepsListItemLinkStyled>
                    </Link>
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled>
                    <Link href="/order/contact-information" passHref>
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 3}>
                            {'3. ' + t('Contact information')}
                        </OrderStepsListItemLinkStyled>
                    </Link>
                </OrderStepsListItemStyled>
            </OrderStepsListStyled>
        </Webline>
    );
};

export default OrderSteps;
