import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { Form, FormContentWrapper } from 'components/Forms/Form/Form';
import { GiftVouchersExceedPayableAmountWarning } from 'components/Pages/Order/GiftVouchersExceedPayableAmountWarning';
import { TIDs } from 'cypress/tids';
import { TypeProductTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { FormProvider } from 'react-hook-form';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ContactInformationFormContent } from './ContactInformationFormContent';
import { useContactInformationForm, useContactInformationFormMeta } from './contactInformationFormMeta';
import { useContactInformationPageNavigation, useCreateOrder } from './contactInformationUtils';
import { ContactInformationSendOrderButton } from './FormBlocks/ContactInformationSendOrderButton';

export const ContactInformationContent: FC = () => {
    const { t } = useTranslation();
    const [formProviderMethods] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta();
    const { goToPreviousStepFromContactInformationPage } = useContactInformationPageNavigation();
    const { createOrder } = useCreateOrder(formProviderMethods, formMeta);
    const { cart } = useCurrentCart();
    const giftVouchersExceedPayableAmount = !!cart?.giftVouchersExceedPayableAmount;
    const cartContainsGiftVoucherProducts = !!cart?.items.some(
        (cartItem) =>
            cartItem.product.productType === TypeProductTypeEnum.ElectronicGiftVoucher ||
            cartItem.product.productType === TypeProductTypeEnum.PrintedGiftVoucher,
    );

    useErrorPopup(formProviderMethods, formMeta.fields, GtmMessageOriginType.contact_information_page);

    return (
        <OrderContentWrapper activeStep={3}>
            <h1 className="sr-only">{t('Contact information')}</h1>

            <FormProvider {...formProviderMethods}>
                <Form
                    preventEnterSubmission
                    formName={formMeta.formName}
                    tid={TIDs.contact_information_form}
                    onSubmit={formProviderMethods.handleSubmit(createOrder)}
                >
                    <FormContentWrapper>
                        {giftVouchersExceedPayableAmount && (
                            <div className="mb-5">
                                <GiftVouchersExceedPayableAmountWarning
                                    cartContainsGiftVoucherProducts={cartContainsGiftVoucherProducts}
                                />
                            </div>
                        )}

                        <ContactInformationFormContent />

                        <ContactInformationSendOrderButton />

                        <OrderAction
                            backStepClickHandler={goToPreviousStepFromContactInformationPage}
                            buttonBack={t('Back')}
                            buttonNext={t('Submit order')}
                            hasDisabledCursor={!formProviderMethods.formState.isValid}
                            hasDisabledLook={!formProviderMethods.formState.isValid || giftVouchersExceedPayableAmount}
                            isDisabled={giftVouchersExceedPayableAmount}
                        />
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </OrderContentWrapper>
    );
};
