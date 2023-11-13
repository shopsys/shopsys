import { useCurrentCart } from 'connectors/cart/Cart';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

const TEST_IDENTIFIER = 'pages-cart-cartpreview';

export const CartPreview: FC = () => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart } = useCurrentCart();

    if (!cart?.items.length) {
        return null;
    }

    return (
        <table className="w-full" data-testid={TEST_IDENTIFIER}>
            <tbody>
                {mapPriceForCalculations(cart.totalDiscountPrice.priceWithVat) > 0 && (
                    <CartPreviewRow dataTestId={TEST_IDENTIFIER + '-discount'}>
                        <CartPreviewCell>{t('The amount of discounts')}</CartPreviewCell>
                        <CartPreviewCell isAlignRight>
                            <strong>{'-' + formatPrice(cart.totalDiscountPrice.priceWithVat)}</strong>
                        </CartPreviewCell>
                    </CartPreviewRow>
                )}
                <CartPreviewRow dataTestId={TEST_IDENTIFIER + '-total'}>
                    <CartPreviewCell>{t('You pay')}</CartPreviewCell>
                    <CartPreviewCell isAlignRight>
                        <strong className="text-2xl text-primary">
                            {formatPrice(cart.totalItemsPrice.priceWithVat)}
                        </strong>
                    </CartPreviewCell>
                </CartPreviewRow>
            </tbody>
        </table>
    );
};

const CartPreviewRow: FC = ({ children, dataTestId }) => (
    <tr className="w-full" data-testid={dataTestId}>
        {children}
    </tr>
);

const CartPreviewCell: FC<{ isAlignRight?: boolean }> = ({ children, isAlignRight }) => (
    <td className={twJoin('py-2 align-baseline text-sm leading-4', isAlignRight && 'text-right')}>{children}</td>
);
