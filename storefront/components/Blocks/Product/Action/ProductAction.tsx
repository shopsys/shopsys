import { AddToCartUnavailableTextStyled, ProductActionStyled, ProductActionWrapperStyled } from './ProductAction.style';
import { AddToCart } from 'components/Blocks/Product/AddToCart/AddToCart';
import { Button } from 'components/Forms/Button/Button';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/dist/client/router';
import { FC } from 'react';
import { GtmListNameType } from 'types/gtm';
import { SliderProductItemType } from 'types/product';

type ProductActionProps = {
    product: SliderProductItemType;
    gtmListName: GtmListNameType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'blocks-product-action';

export const ProductAction: FC<ProductActionProps> = ({ product, gtmListName, listIndex }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();

    if (product.isMainVariant) {
        return (
            <ProductActionStyled isButtonFullWidth>
                <Button
                    type="button"
                    onClick={() => router.push(product.slug)}
                    name="choose-variant"
                    testIdentifier={TEST_IDENTIFIER + '-choose-variant'}
                >
                    {t('Choose variant')}
                </Button>
            </ProductActionStyled>
        );
    }

    if (product.isSellingDenied) {
        return (
            <ProductActionWrapperStyled data-testid={TEST_IDENTIFIER}>
                <ProductActionStyled isButtonFullWidth={false}>
                    <AddToCartUnavailableTextStyled>
                        {t('This item can no longer be purchased')}
                    </AddToCartUnavailableTextStyled>
                </ProductActionStyled>
            </ProductActionWrapperStyled>
        );
    }

    return (
        <ProductActionWrapperStyled data-testid={TEST_IDENTIFIER}>
            <ProductActionStyled isButtonFullWidth={false}>
                <AddToCart
                    productUuid={product.uuid}
                    minQuantity={1}
                    maxQuantity={product.stockQuantity}
                    gtmListName={gtmListName}
                    listIndex={listIndex}
                />
            </ProductActionStyled>
        </ProductActionWrapperStyled>
    );
};
