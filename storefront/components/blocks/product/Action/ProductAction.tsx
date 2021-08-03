import { FormProvider, useForm } from 'react-hook-form';
import { FC } from 'react';
import { ProductItemType } from '../types';
import ShopsysButton from '../../../forms/ShopsysButton';
import ShopsysLink from '../../../basic/ShopsysLink';
import ShopsysTextInput from '../../../forms/ShopsysTextInput';
import { useTranslation } from 'react-i18next';

const ProductAction: FC<ProductItemType> = (props) => {
    const { t } = useTranslation();
    const formProviderMethods = useForm({
        mode: 'onBlur',
        criteriaMode: 'firstError',
        shouldFocusError: true,
    });

    if (props.isMainVariant) {
        return (
            <div>
                <ShopsysLink href={props.detailSlug}>
                    <div>{t('Choose variant')}</div>
                </ShopsysLink>
            </div>
        );
    }
    return (
        /** TODO KOD should be probably changed to ShopsysForm */
        <FormProvider {...formProviderMethods}>
            <form>
                <ShopsysTextInput id="addToCartQuantity" name="addToCartQuantity" label="quantity" required={true} />
                <ShopsysButton name="add-to-cart">{t<string>('Add to cart')}</ShopsysButton>
            </form>
        </FormProvider>
    );
};

export default ProductAction;
