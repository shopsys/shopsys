import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';

export const CartPreview: FC = () => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart } = useCurrentCart();

    if (!cart?.items.length) {
        return null;
    }

    return (
        <table className="w-full">
            <tbody>
                {mapPriceForCalculations(cart.totalDiscountPrice.priceWithVat) > 0 && (
                    <CartPreviewRow tid={TIDs.pages_cart_cartpreview_discount}>
                        <CartPreviewCell>{t('The amount of discounts')}</CartPreviewCell>
                        <CartPreviewCell isAlignRight>
                            <strong>{'-' + formatPrice(cart.totalDiscountPrice.priceWithVat)}</strong>
                        </CartPreviewCell>
                    </CartPreviewRow>
                )}
                <CartPreviewRow tid={TIDs.pages_cart_cartpreview_total}>
                    <CartPreviewCell>{t('You pay')}</CartPreviewCell>
                    <CartPreviewCell isAlignRight>
                        <strong className="text-2xl text-primaryDark">
                            {formatPrice(cart.totalItemsPrice.priceWithVat)}
                        </strong>
                    </CartPreviewCell>
                </CartPreviewRow>
            </tbody>
        </table>
    );
};

const CartPreviewRow: FC = ({ children, tid }) => (
    <tr className="w-full" tid={tid}>
        {children}
    </tr>
);

const CartPreviewCell: FC<{ isAlignRight?: boolean }> = ({ children, isAlignRight }) => (
    <td className={twJoin('py-2 align-baseline text-sm leading-4', isAlignRight && 'text-right')}>{children}</td>
);
