import { OrderStepsListItemLinkStyled, OrderStepsListItemStyled, OrderStepsListStyled } from './OrderSteps.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

type OrderStepsProps = {
    activeStep: number;
    domainUrl: string;
};

export const OrderSteps: FC<OrderStepsProps> = (props) => {
    const testIdentifier = 'blocks-ordersteps-';

    const t = useTypedTranslationFunction();
    const [cartUrl, transportAndPaymentUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment'],
        props.domainUrl,
    );

    return (
        <Webline>
            <OrderStepsListStyled>
                <OrderStepsListItemStyled data-testid={testIdentifier + '1'}>
                    {props.activeStep > 1 ? (
                        <NextLink href={cartUrl} passHref prefetch>
                            <OrderStepsListItemLinkStyled isActive={false} cursor="pointer">
                                {'1. ' + t('Cart')}
                            </OrderStepsListItemLinkStyled>
                        </NextLink>
                    ) : (
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 1}>
                            {'1. ' + t('Cart')}
                        </OrderStepsListItemLinkStyled>
                    )}
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled data-testid={testIdentifier + '2'}>
                    {props.activeStep > 2 ? (
                        <NextLink href={transportAndPaymentUrl} passHref prefetch>
                            <OrderStepsListItemLinkStyled isActive={false} cursor="pointer">
                                {'2. ' + t('Transport and payment')}
                            </OrderStepsListItemLinkStyled>
                        </NextLink>
                    ) : (
                        <OrderStepsListItemLinkStyled isActive={props.activeStep === 2}>
                            {'2. ' + t('Transport and payment')}
                        </OrderStepsListItemLinkStyled>
                    )}
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled data-testid={testIdentifier + '3'}>
                    <OrderStepsListItemLinkStyled isActive={props.activeStep === 3}>
                        {'3. ' + t('Contact information')}
                    </OrderStepsListItemLinkStyled>
                </OrderStepsListItemStyled>
            </OrderStepsListStyled>
        </Webline>
    );
};
