import { ProductActionStyled, ProductActionWrapperStyled } from './ProductAction.style';
import Button from '../../../Forms/Button';
import { FC } from 'react';
import { SliderProductItemType } from '../types';
import Spinbox from '../../../Forms/Spinbox';
import { useRouter } from 'next/dist/client/router';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const ProductAction: FC<SliderProductItemType> = (props) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();

    if (props.isMainVariant) {
        return (
            <ProductActionStyled isButtonFullWidth={true}>
                <Button type="button" onClick={() => router.push(props.detailSlug)} name="choose-variant">
                    {t('Choose variant')}
                </Button>
            </ProductActionStyled>
        );
    }

    return (
        <ProductActionWrapperStyled>
            <ProductActionStyled isButtonFullWidth={false}>
                <Spinbox size="small" step={1} min={1} max={15} defaultValue={1} />
                <Button type="button" size="small" name="add-to-cart">
                    {t('Add to cart')}
                </Button>
            </ProductActionStyled>
        </ProductActionWrapperStyled>
    );
};

export default ProductAction;
