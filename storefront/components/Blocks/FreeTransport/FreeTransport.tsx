import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { FreeTransportStyled } from './FreeTransport.style';
import { Trans } from 'react-i18next';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const FreeTransport: FC = () => {
    const { cart } = useShopsysSelector((state) => state.user);
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const amount = cart?.remainingAmountWithVatForFreeTransport;

    if (amount === null || amount === undefined) {
        return null;
    }

    const amountFormatted = formatPrice(amount, domainConfig.currencyCode, t);

    if (amount > 0) {
        return (
            <FreeTransportStyled>
                <Trans i18nKey="FreeTransportAmountLeft">
                    Add products for
                    <strong>{{ amountFormatted }}</strong>
                    more, and enjoy <strong>free delivery!</strong>
                </Trans>
            </FreeTransportStyled>
        );
    }

    return (
        <FreeTransportStyled>
            <strong>{t('Your delivery and payment is now free of charge!')}</strong>
        </FreeTransportStyled>
    );
};

export default FreeTransport;
