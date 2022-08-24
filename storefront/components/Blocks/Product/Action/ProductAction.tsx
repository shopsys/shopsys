import { AddToCartUnavailableTextStyled, ProductActionStyled, ProductActionWrapperStyled } from './ProductAction.style';
import { AddToCart } from 'components/Blocks/Product/AddToCart/AddToCart';
import { Button } from 'components/Forms/Button/Button';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/dist/client/router';
import { FC } from 'react';
import { GtmListNameType } from 'types/gtm';
import { SliderProductItemType } from 'types/product';

type ProductActionProps = {
    product: SliderProductItemType;
    gtmListName: GtmListNameType;
    listIndex: number;
};

export const ProductAction: FC<ProductActionProps> = (props) => {
    const testIdentifier = 'blocks-product-action';

    const router = useRouter();
    const t = useTypedTranslationFunction();

    if (props.product.isMainVariant) {
        return (
            <ProductActionStyled isButtonFullWidth={true}>
                <Button
                    type="button"
                    onClick={() => router.push(props.product.slug)}
                    name="choose-variant"
                    data-testid={testIdentifier + '-choose-variant'}
                >
                    {t('Choose variant')}
                </Button>
            </ProductActionStyled>
        );
    }

    if (props.product.isSellingDenied) {
        return (
            <ProductActionWrapperStyled data-testid={testIdentifier}>
                <ProductActionStyled isButtonFullWidth={false}>
                    <AddToCartUnavailableTextStyled>
                        {t('This item can no longer be purchased')}
                    </AddToCartUnavailableTextStyled>
                </ProductActionStyled>
            </ProductActionWrapperStyled>
        );
    }

    return (
        <ProductActionWrapperStyled data-testid={testIdentifier}>
            <ProductActionStyled isButtonFullWidth={false}>
                <AddToCart
                    productUuid={props.product.uuid}
                    productName={props.product.fullName}
                    minQuantity={1}
                    maxQuantity={props.product.stockQuantity}
                    gtmListName={props.gtmListName}
                    listIndex={props.listIndex}
                />
            </ProductActionStyled>
        </ProductActionWrapperStyled>
    );
};
