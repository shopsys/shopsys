import { OrderStepsListItemLinkStyled, OrderStepsListItemStyled, OrderStepsListStyled } from './OrderSteps.style';
import { FC } from 'react';
import NextLink from 'next/link';
import { useGetInternationalizedStaticUrls } from 'hooks/UseGetInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type OrderStepsProps = {
    activeStep: number;
    domainUrl: string;
};

const OrderSteps: FC<OrderStepsProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [cartUrl, shipmentAndPaymentUrl, contactInformationUrl] = useGetInternationalizedStaticUrls(
        ['/cart', '/order/shipment-and-payment', '/order/contact-information'],
        props.domainUrl,
    );

    return (
        <Webline>
            <OrderStepsListStyled>
                <OrderStepsListItemStyled>
                    <NextLink href={cartUrl} passHref>
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 1}>
                            {'1. ' + t('Cart')}
                        </OrderStepsListItemLinkStyled>
                    </NextLink>
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled>
                    <NextLink href={shipmentAndPaymentUrl} passHref>
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 2}>
                            {'2. ' + t('Shipment and payment')}
                        </OrderStepsListItemLinkStyled>
                    </NextLink>
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled>
                    <NextLink href={contactInformationUrl} passHref>
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 3}>
                            {'3. ' + t('Contact information')}
                        </OrderStepsListItemLinkStyled>
                    </NextLink>
                </OrderStepsListItemStyled>
            </OrderStepsListStyled>
        </Webline>
    );
};

export default OrderSteps;
