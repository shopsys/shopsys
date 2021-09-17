import { FC, useRef } from 'react';
import { ProductActionStyled, ProductActionWrapperStyled } from './ProductAction.style';
import Button from '../../../Forms/Button';
import { SliderProductItemType } from '../types';
import Spinbox from '../../../Forms/Spinbox';
import { useRouter } from 'next/dist/client/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ProductAction: FC<SliderProductItemType> = (props) => {
    const router = useRouter();
    const spinboxRef = useRef<HTMLInputElement | null>(null);
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
                {/* TODO change maximum for spinbox to be taken from API */}
                <Spinbox size="small" step={1} min={1} max={15} defaultValue={1} ref={spinboxRef} />
                <Button type="button" size="small" name="add-to-cart">
                    {t('Add to cart')}
                </Button>
            </ProductActionStyled>
        </ProductActionWrapperStyled>
    );
};

export default ProductAction;
