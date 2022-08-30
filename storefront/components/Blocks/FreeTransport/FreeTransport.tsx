import { FreeTransportStyled } from './FreeTransport.style';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { FC } from 'react';

const TEST_IDENTIFIER = 'blocks-freetransport';

export const FreeTransport: FC = () => {
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
            <FreeTransportStyled data-testid={TEST_IDENTIFIER}>
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
        <FreeTransportStyled data-testid={TEST_IDENTIFIER}>
            <strong>{t('Your delivery and payment is now free of charge!')}</strong>
        </FreeTransportStyled>
    );
};
