import { OrderStepsListItemLinkStyled, OrderStepsListItemStyled, OrderStepsListStyled } from './OrderSteps.style';
import { FC } from 'react';
import Link from 'next/link';
import { useGetInternationalizedStaticUrls } from 'hooks/UseGetInternationalizedStaticUrls';
import { useTranslation } from 'next-i18next';
import Webline from 'components/Layout/Webline';

type OrderStepsProps = {
    activeStep: number;
    domainUrl: string;
};

const OrderSteps: FC<OrderStepsProps> = (props) => {
    const { t } = useTranslation();
    const [cartUrl, shipmentAndPaymentUrl, contactInformationUrl] = useGetInternationalizedStaticUrls(
        ['/cart', '/order/shipment-and-payment', '/order/contact-information'],
        props.domainUrl,
    );

    return (
        <Webline>
            <OrderStepsListStyled>
                <OrderStepsListItemStyled>
                    <Link href={cartUrl} passHref>
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 1}>
                            {'1. ' + t('Cart')}
                        </OrderStepsListItemLinkStyled>
                    </Link>
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled>
                    <Link href={shipmentAndPaymentUrl} passHref>
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 2}>
                            {'2. ' + t('Shipment and payment')}
                        </OrderStepsListItemLinkStyled>
                    </Link>
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled>
                    <Link href={contactInformationUrl} passHref>
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
