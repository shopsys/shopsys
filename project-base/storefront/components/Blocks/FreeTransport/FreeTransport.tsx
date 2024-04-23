import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

export const FreeTransport: FC = () => {
    const { cart } = useCurrentCart();
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const amount = cart?.remainingAmountWithVatForFreeTransport;

    if (!cart?.items.length || amount === null || amount === undefined) {
        return null;
    }

    const amountFormatted = formatPrice(amount);

    if (parseInt(amount) > 0) {
        return (
            <Wrapper>
                <Trans
                    i18nKey="FreeTransportAmountLeft"
                    values={{ amountFormatted: amountFormatted }}
                    components={{
                        0: <strong />,
                    }}
                />
            </Wrapper>
        );
    }

    return (
        <Wrapper>
            <strong>{t('Your delivery and payment is now free of charge!')}</strong>
        </Wrapper>
    );
};

const Wrapper: FC = ({ children }) => (
    <div className="my-2 block rounded bg-primary text-white px-3 py-1 text-xs [&_strong]:font-bold [&_strong]:text-green">
        {children}
    </div>
);
