import { FormProvider, useForm } from 'react-hook-form';
import { FC } from 'react';
import Link from 'next/link';
import { ProductActionStyled } from './ProductAction.style';
import ShopsysButton from '../../../forms/ShopsysButton';
import ShopsysSpinbox from '../../../forms/ShopsysSpinbox';
import { SliderProductItemType } from '../types';
import { useTranslation } from 'react-i18next';

const ProductAction: FC<SliderProductItemType> = (props) => {
    const { t } = useTranslation();
    const formProviderMethods = useForm({
        mode: 'onBlur',
        criteriaMode: 'firstError',
        shouldFocusError: true,
    });

    if (props.isMainVariant) {
        return (
            <ProductActionStyled>
                <Link href={props.detailSlug} passHref>
                    <ShopsysButton name="choose-variant">{t<string>('Choose variant')}</ShopsysButton>
                </Link>
            </ProductActionStyled>
        );
    }
    return (
        /** TODO KOD should be probably changed to ShopsysForm */
        <ProductActionStyled>
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
