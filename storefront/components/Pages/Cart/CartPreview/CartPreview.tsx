import { useCurrentCart } from 'connectors/cart/Cart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { twJoin } from 'tailwind-merge';

const TEST_IDENTIFIER = 'pages-cart-cartpreview';

export const CartPreview: FC = () => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const { cart, isCartEmpty } = useCurrentCart();

    if (cart === null || isCartEmpty) {
        return null;
    }

    return (
        <table className="w-full" data-testid={TEST_IDENTIFIER}>
            <tbody>
                {cart.totalDiscountPrice.priceWithVat > 0 && (
                    <CartPreviewRow dataTestid={TEST_IDENTIFIER + '-discount'}>
                        <CartPreviewCell>{t('The amount of discounts')}</CartPreviewCell>
                        <CartPreviewCell isAlignRight>
                            <strong>{'-' + formatPrice(cart.totalDiscountPrice.priceWithVat)}</strong>
                        </CartPreviewCell>
                    </CartPreviewRow>
                )}
                <CartPreviewRow dataTestid={TEST_IDENTIFIER + '-total'}>
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

const CartPreviewRow: FC<{ dataTestid: string }> = ({ children, dataTestid }) => (
    <tr className="w-full" data-testid={dataTestid}>
        {children}
    </tr>
);

const CartPreviewCell: FC<{ isAlignRight?: boolean }> = ({ children, isAlignRight }) => (
    <td className={twJoin('py-2 align-baseline text-sm leading-4', isAlignRight && 'text-right')}>{children}</td>
);
