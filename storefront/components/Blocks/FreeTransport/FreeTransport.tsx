import { FreeTransportStyled } from './FreeTransport.style';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { FC } from 'react';

const FreeTransport: FC = () => {
    const testIdentifier = 'blocks-freetransport';

    const { cart, isCartEmpty } = useCurrentCart();
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const amount = cart?.remainingAmountWithVatForFreeTransport;

    if (isCartEmpty || amount === null || amount === undefined) {
        return null;
    }

    const amountFormatted = formatPrice(amount);

    if (amount > 0) {
        return (
            <FreeTransportStyled data-testid={testIdentifier}>
                <Trans
                    i18nKey="FreeTransportAmountLeft"
                    components={{
                        0: <strong />,
                    }}
                    values={{ amountFormatted: amountFormatted }}
                />
            </FreeTransportStyled>
        );
    }

    return (
        <FreeTransportStyled data-testid={testIdentifier}>
            <strong>{t('Your delivery and payment is now free of charge!')}</strong>
        </FreeTransportStyled>
    );
};

export default FreeTransport;
