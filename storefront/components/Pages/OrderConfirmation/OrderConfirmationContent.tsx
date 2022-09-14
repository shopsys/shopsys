import {
    ImageWrapperStyled,
    MessageStyled,
    MessageTextStyled,
    MessageWrapperStyled,
} from './OrderConfirmationContent.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { useOrderSentPageContentApi } from 'graphql/generated';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { FC } from 'react';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { PaymentTypeEnum } from 'types/payment';

const TEST_IDENTIFIER = 'pages-orderconfirmation';

export const OrderConfirmationContent: FC = () => {
    const dispatch = useShopsysDispatch();
    const { lastOrderUuid, lastOrderPaymentType } = useShopsysSelector((state) => state.user);
    const [{ data }] = useOrderSentPageContentApi({ variables: { orderUuid: lastOrderUuid } });

    useEffectOnce(() => {
        return () => {
            dispatch(userActions.setOrderUrlHash(undefined));
        };
    });

    return (
        <Webline>
            <MessageWrapperStyled data-testid={TEST_IDENTIFIER}>
                <ImageWrapperStyled>
                    <img alt="Objednávka odeslána" src="/public/frontend/images/sent-cart.svg" />
                </ImageWrapperStyled>
                <MessageStyled>
                    {data !== undefined && (
                        <MessageTextStyled dangerouslySetInnerHTML={{ __html: data.orderSentPageContent }} />
                    )}
                    {lastOrderPaymentType === PaymentTypeEnum.GoPay && <GoPayGateway orderUuid={lastOrderUuid} />}
                </MessageStyled>
            </MessageWrapperStyled>
        </Webline>
    );
};
