import { PromoCodeInfo } from './PromoCodeInfo';
import { usePromoCodeForm, usePromoCodeFormMeta } from './promoCodeFormMeta';
import { PlusIcon } from 'components/Basic/Icon/PlusIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { FormProvider } from 'react-hook-form';
import { useApplyPromoCodeToCart } from 'utils/cart/useApplyPromoCodeToCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemovePromoCodeFromCart } from 'utils/cart/useRemovePromoCodeFromCart';
import { useErrorPopup } from 'utils/forms/useErrorPopup';

export const PromoCode: FC = () => {
    const { promoCodes } = useCurrentCart();
    const [formProviderMethods, defaultValues] = usePromoCodeForm();
    const formMeta = usePromoCodeFormMeta(formProviderMethods);
    const { t } = useTranslation();
    const { applyPromoCodeToCart, isApplyingPromoCodeToCart } = useApplyPromoCodeToCart({
        success: t('Promo code was added to the order.'),
        error: t('There was an error while adding a promo code to the order.'),
    });
    const { removePromoCodeFromCart, isRemovingPromoCodeFromCart } = useRemovePromoCodeFromCart({
        success: t('Promo code was removed from the order.'),
        error: t('There was an error while removing the promo code from the order.'),
    });
    useErrorPopup(formProviderMethods, formMeta.fields);

    const [isContentVisible, setIsContentVisible] = useState(!!defaultValues.promoCode);

    return (
        <div>
            {promoCodes.length ? (
                <>
                    {isRemovingPromoCodeFromCart && <LoaderWithOverlay className="w-5" />}
                    {promoCodes.map(({ code }) => (
                        <PromoCodeInfo
                            key={code}
                            promoCode={code}
                            onRemovePromoCodeCallback={() => removePromoCodeFromCart(code)}
                        />
                    ))}
                </>
            ) : (
                <>
                    <Button
                        className="mb-3 text-sm max-sm:w-full"
                        tid={TIDs.blocks_promocode_add_button}
                        variant="inverted"
                        onClick={() => setIsContentVisible(!isContentVisible)}
                    >
                        <PlusIcon className="w-3" />
                        {t('I have a discount coupon')}
                    </Button>
                    {isContentVisible && (
                        <FormProvider {...formProviderMethods}>
                            <Form
                                className="mt-15 sm:mt-0"
                                onSubmit={formProviderMethods.handleSubmit((promoCodeFormData) =>
                                    applyPromoCodeToCart(promoCodeFormData.promoCode),
                                )}
                            >
                                <div className="flex max-w-sm">
                                    <TextInputControlled
                                        isWithoutFormLineError
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.promoCode.name}
                                        render={(textInput) => textInput}
                                        textInputProps={{
                                            label: formMeta.fields.promoCode.label,
                                            required: true,
                                            className: '!rounded-r-none border-r-0',
                                        }}
                                    />
                                    <SubmitButton
                                        className="h-auto !rounded-l-none !rounded-r !px-3"
                                        isWithDisabledLook={!formProviderMethods.formState.isValid}
                                        tid={TIDs.blocks_promocode_apply_button}
                                        variant="inverted"
                                    >
                                        {isApplyingPromoCodeToCart && <Loader className="w-4" />}

                                        {t('Apply')}
                                    </SubmitButton>
                                </div>
                            </Form>
                        </FormProvider>
                    )}
                </>
            )}
        </div>
    );
};
