import { useCurrentCart } from 'connectors/cart/Cart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';

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

    if (parseInt(amount) > 0) {
        return (
            <Wrapper dataTestId={TEST_IDENTIFIER}>
                <Trans
                    i18nKey="FreeTransportAmountLeft"
                    components={{
                        0: <strong />,
                    }}
                    values={{ amountFormatted: amountFormatted }}
                />
            </Wrapper>
        );
    }

    return (
        <Wrapper dataTestId={TEST_IDENTIFIER}>
            <strong>{t('Your delivery and payment is now free of charge!')}</strong>
        </Wrapper>
    );
};

const Wrapper: FC = ({ children, dataTestId }) => (
    <div
        className="my-2 block rounded bg-greenVeryLight px-3 py-1 text-xs [&_strong]:font-bold [&_strong]:text-greenDark"
        data-testid={dataTestId}
    >
        {children}
    </div>
);
