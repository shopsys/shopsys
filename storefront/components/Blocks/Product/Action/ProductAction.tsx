import { FormProvider, useForm } from 'react-hook-form';
import Button from '../../../Forms/Button';
import { FC } from 'react';
import { ProductActionStyled } from './ProductAction.style';
import ShopsysSpinbox from '../../../Forms/ShopsysSpinbox';
import { SliderProductItemType } from '../types';
import { useRouter } from 'next/dist/client/router';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const ProductAction: FC<SliderProductItemType> = (props) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const formProviderMethods = useForm({
        mode: 'onBlur',
        criteriaMode: 'firstError',
        shouldFocusError: true,
    });

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
        /** TODO KOD should be probably changed to ShopsysForm */
        <ProductActionStyled isButtonFullWidth={false}>
            <FormProvider {...formProviderMethods}>
                <form>
                    <ShopsysSpinbox size="small" />
                    <Button type="button" size="small" name="add-to-cart">
                        {t('Add to cart')}
                    </Button>
                </form>
            </FormProvider>
        </ProductActionStyled>
    );
};

export default ProductAction;
