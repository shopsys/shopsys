import { PayOrderMutationApi, usePayOrderMutationApi } from 'graphql/generated';
import { canUseDom } from 'helpers/canUseDom';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { FC, useEffect } from 'react';

type GoPayGatewayProps = {
    orderUuid?: string;
};

const GoPayGateway: FC<GoPayGatewayProps> = (props) => {
    const t = useTypedTranslationFunction();
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
        }
    }, [payOrderResult]);

    return (
        <form
            action={payOrderResult.data?.PayOrder.goPayCreatePaymentSetup?.gatewayUrl}
            method="post"
            id="gopay-payment-button"
        >
            <button name="pay" type="submit">
                {t('Pay with GoPay')}
            </button>
        </form>
    );
};

export default GoPayGateway;
