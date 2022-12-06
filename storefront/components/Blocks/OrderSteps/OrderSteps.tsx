import { OrderStepsListItemLinkStyled, OrderStepsListItemStyled, OrderStepsListStyled } from './OrderSteps.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';

type OrderStepsProps = {
    activeStep: number;
    domainUrl: string;
};

const TEST_IDENTIFIER = 'blocks-ordersteps-';

export const OrderSteps: FC<OrderStepsProps> = ({ activeStep, domainUrl }) => {
    const t = useTypedTranslationFunction();
    const [cartUrl, transportAndPaymentUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/transport-and-payment'],
        domainUrl,
    );

    return (
        <Webline>
            <OrderStepsListStyled>
                <OrderStepsListItemStyled data-testid={TEST_IDENTIFIER + '1'}>
                    {activeStep > 1 ? (
                        <NextLink href={cartUrl} passHref>
                            <OrderStepsListItemLinkStyled isActive={false} cursor="pointer">
                                {'1. ' + t('Cart')}
                            </OrderStepsListItemLinkStyled>
                        </NextLink>
                    ) : (
                        <OrderStepsListItemLinkStyled isActive={activeStep === 1}>
                            {'1. ' + t('Cart')}
                        </OrderStepsListItemLinkStyled>
                    )}
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled data-testid={TEST_IDENTIFIER + '2'}>
                    {activeStep > 2 ? (
                        <NextLink href={transportAndPaymentUrl} passHref>
                            <OrderStepsListItemLinkStyled isActive={false} cursor="pointer">
                                {'2. ' + t('Transport and payment')}
                            </OrderStepsListItemLinkStyled>
                        </NextLink>
                    ) : (
                        <OrderStepsListItemLinkStyled isActive={activeStep === 2}>
                            {'2. ' + t('Transport and payment')}
                        </OrderStepsListItemLinkStyled>
                    )}
                </OrderStepsListItemStyled>
                <OrderStepsListItemStyled data-testid={TEST_IDENTIFIER + '3'}>
                    <OrderStepsListItemLinkStyled isActive={activeStep === 3}>
                        {'3. ' + t('Contact information')}
                    </OrderStepsListItemLinkStyled>
                </OrderStepsListItemStyled>
            </OrderStepsListStyled>
        </Webline>
    );
};
