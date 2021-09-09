import { FormProvider, useForm } from 'react-hook-form';
import { FC } from 'react';
import { ProductActionStyled } from './ProductAction.style';
import ShopsysButton from '../../../Forms/ShopsysButton';
import ShopsysSpinbox from '../../../Forms/ShopsysSpinbox';
import { SliderProductItemType } from '../types';
import { useRouter } from 'next/dist/client/router';
import { useTranslation } from 'react-i18next';

const ProductAction: FC<SliderProductItemType> = (props) => {
    const router = useRouter();
    const { t } = useTranslation();
    const formProviderMethods = useForm({
        mode: 'onBlur',
        criteriaMode: 'firstError',
        shouldFocusError: true,
    });

    if (props.isMainVariant) {
        return (
            <ProductActionStyled isButtonFullWidth={true}>
                <ShopsysButton onClick={() => router.push(props.detailSlug)} name="choose-variant">
                    {t<string>('Choose variant')}
                </ShopsysButton>
            </ProductActionStyled>
        );
    }
    return (
        /** TODO KOD should be probably changed to ShopsysForm */
        <ProductActionStyled isButtonFullWidth={false}>
            <FormProvider {...formProviderMethods}>
                <form>
                    <ShopsysSpinbox size="small" />
                    <ShopsysButton size="small" name="add-to-cart">
                        {t<string>('Add to cart')}
                    </ShopsysButton>
                </form>
            </FormProvider>
        </ProductActionStyled>
    );
};

export default ProductAction;
