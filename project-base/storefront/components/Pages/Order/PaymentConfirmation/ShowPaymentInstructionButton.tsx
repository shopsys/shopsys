import { Button } from 'components/Forms/Button/Button';
import { useSetOrderPaymentStatusPageValidityHashMutation } from 'graphql/requests/orders/mutations/SetOrderPaymentStatusPageValidityHashMutation.generated';
import useTranslation from 'next-translate/useTranslation';
import Script from 'next/script';
import { useState } from 'react';

type ShowPaymentInstructionButtonProps = {
    href: string;
    orderUuid: string;
    orderPaymentStatusPageValidityHash: string;
};

export const ShowPaymentInstructionButton: FC<ShowPaymentInstructionButtonProps> = ({
    href,
    orderUuid,
    orderPaymentStatusPageValidityHash,
}) => {
    const { t } = useTranslation();
    const [, setOrderPaymentStatusPageValidityHashMutation] = useSetOrderPaymentStatusPageValidityHashMutation();
    const [goPayEmbedJs, setGoPayEmbedJs] = useState<string | undefined>(undefined);

    const initGoPayCheckout = (gatewayUrl: string) => () => {
        // @ts-expect-error 3rd party function
        _gopay.checkout({
            gatewayUrl,
            inline: true,
        });
    };

    const handleShowPaymentInstruction = () => {
        setOrderPaymentStatusPageValidityHashMutation({
            orderUuid,
            orderPaymentStatusPageValidityHash,
        }).then((result) => {
            setGoPayEmbedJs(result.data?.SetOrderPaymentStatusPageValidityHashMutation);
        });
    };

    return (
        <>
            {goPayEmbedJs && <Script id="go-pay-embedded-js" src={goPayEmbedJs} onLoad={initGoPayCheckout(href)} />}
            <Button onClick={handleShowPaymentInstruction}>{t('Show payment instruction')}</Button>
        </>
    );
};
