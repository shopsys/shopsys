import Button from 'components/Forms/Button';
import { PayOrderMutationApi, usePayOrderMutationApi } from 'graphql/generated';
import { canUseDom } from 'helpers/canUseDom';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useRouter } from 'next/router';
import { FC, useEffect } from 'react';

type GoPayGatewayProps = {
    orderUuid?: string;
};

const GoPayGateway: FC<GoPayGatewayProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { push } = useRouter();
    const [payOrderResult, payOrder] = usePayOrderMutationApi();

    const getGatewayVariables = async (orderUuid: string) => {
        await payOrder({ orderUuid });
    };

    const goPayInit = (goPayCreatePaymentSetup: PayOrderMutationApi['PayOrder']['goPayCreatePaymentSetup']): void => {
        if (!canUseDom()) {
            return;
        }

        const existingScript = document.getElementById('goPayEmbedJs');

        if (existingScript === null && goPayCreatePaymentSetup !== null) {
            const goPayEmbedJsScriptTag = document.createElement('script');
            goPayEmbedJsScriptTag.src = goPayCreatePaymentSetup.embedJs;
            goPayEmbedJsScriptTag.id = 'goPayEmbedJs';

            document.body.appendChild(goPayEmbedJsScriptTag);

            goPayEmbedJsScriptTag.onload = () => {
                // @ts-expect-error 3rd party function
                _gopay.checkout({
                    gatewayUrl: goPayCreatePaymentSetup.gatewayUrl,
                    inline: true,
                });
            };
        }
    };

    useEffectOnce(() => {
        if (props.orderUuid !== undefined) {
            getGatewayVariables(props.orderUuid);
        }
    });

    useEffect(() => {
        {
            goPayInit(payOrderResult.data?.PayOrder.goPayCreatePaymentSetup ?? null);
            if (payOrderResult.data?.PayOrder.goPayCreatePaymentSetup?.gatewayUrl !== undefined) {
                push(payOrderResult.data.PayOrder.goPayCreatePaymentSetup.gatewayUrl);
            }
        }
    }, [payOrderResult, push]);

    return (
        <form
            action={payOrderResult.data?.PayOrder.goPayCreatePaymentSetup?.gatewayUrl}
            method="post"
            id="gopay-payment-button"
        >
            <Button data-testid="pages-order-paymentconfirmation-pay" name="pay" type="submit">
                {t('Pay with GoPay')}
            </Button>
        </form>
    );
};

export default GoPayGateway;
