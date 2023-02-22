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
            <div
                className="mt-16 mb-10 flex flex-col items-center justify-center lg:mb-20 lg:flex-row"
                data-testid={TEST_IDENTIFIER}
            >
                <div className="w-40 lg:mr-32">
                    <img alt="Objednávka odeslána" src="/public/frontend/images/sent-cart.svg" />
                </div>
                <div className="text-center lg:text-left">
                    {data !== undefined && (
                        <div className="mb-8" dangerouslySetInnerHTML={{ __html: data.orderSentPageContent }} />
                    )}
                    {lastOrderPaymentType === PaymentTypeEnum.GoPay && <GoPayGateway orderUuid={lastOrderUuid} />}
                </div>
            </div>
        </Webline>
    );
};
