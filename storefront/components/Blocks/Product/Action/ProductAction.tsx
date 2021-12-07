import { ProductActionStyled, ProductActionWrapperStyled } from './ProductAction.style';
import AddToCart from 'components/Blocks/Product/AddToCart/AddToCart';
import Button from 'components/Forms/Button';
import { FC } from 'react';
import { SliderProductItemType } from 'components/Blocks/Product/types';
import { useRouter } from 'next/dist/client/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ProductAction: FC<SliderProductItemType> = (props) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();

    if (props.isMainVariant) {
        return (
            <ProductActionStyled isButtonFullWidth={true}>
                <Button type="button" onClick={() => router.push(props.slug)} name="choose-variant">
                    {t('Choose variant')}
                </Button>
            </ProductActionStyled>
        );
    }

    return (
        <ProductActionWrapperStyled>
            <ProductActionStyled isButtonFullWidth={false}>
                <AddToCart
                    productUuid={props.uuid}
                    productName={props.name}
                    minQuantity={1}
                    maxQuantity={props.stockQuantity}
                />
            </ProductActionStyled>
        </ProductActionWrapperStyled>
    );
};

export default ProductAction;
