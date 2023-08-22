import { Link } from 'components/Basic/Link/Link';
import { showErrorMessage } from 'helpers/toasts';
import { GoPayCreatePaymentSetupApi, usePayOrderMutationApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import Script from 'next/script';
import { useEffect, useState } from 'react';

type GoPayGatewayProps = {
    orderUuid: string;
};

export const GoPayGateway: FC<GoPayGatewayProps> = ({ orderUuid }) => {
    const { t } = useTranslation();
    const [isRedirectLinkVisible, setRedirectLinkVisibility] = useState(false);
    const [goPayPaymentSetup, setGoPayPaymentSetup] = useState<GoPayCreatePaymentSetupApi | undefined>(undefined);
    const [, payOrder] = usePayOrderMutationApi();

    useEffect(() => {
        payOrder({ orderUuid }).then((payOrderResult) => {
            if (payOrderResult.error?.graphQLErrors !== undefined) {
                for (const error of payOrderResult.error.graphQLErrors) {
                    showErrorMessage(error.message);
                }

                return;
            }

            setTimeout(() => setRedirectLinkVisibility(true), 4000);
            setGoPayPaymentSetup(payOrderResult.data?.PayOrder.goPayCreatePaymentSetup ?? undefined);
        });
    }, []);

    const initGoPayCheckout = (gatewayUrl: string) => () => {
        // @ts-expect-error 3rd party function
        _gopay.checkout({
            gatewayUrl,
            inline: true,
        });
    };

    if (!goPayPaymentSetup) {
        return null;
    }

    return (
        <>
            <Script
                id="go-pay-embedded-js"
                src={goPayPaymentSetup.embedJs}
                onLoad={initGoPayCheckout(goPayPaymentSetup.gatewayUrl)}
            ></Script>
            {isRedirectLinkVisible && (
                <Link href={goPayPaymentSetup.gatewayUrl} isButton isExternal>
                    {t('Pay with GoPay')}
                </Link>
            )}
        </>
    );
};
