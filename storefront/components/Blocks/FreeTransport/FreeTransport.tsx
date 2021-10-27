import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { FreeTransportStyled } from './FreeTransport.style';
import { Trans } from 'react-i18next';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type FreeTransportProps = {
    /**
     * A prop to define the price remaining to reach free transport.
     */
    amountLeft: number;
};

const FreeTransport: FC<FreeTransportProps> = (props) => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const amountLeftvalue = formatPrice(props.amountLeft, domainConfig.currencyCode, t);
    if (props.amountLeft > 0) {
        return (
            <FreeTransportStyled>
                <Trans i18nKey="FreeTransportAmountLeft">
                    Add products for
                    <strong>{{ amountLeftvalue }}</strong>
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
