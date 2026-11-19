import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { Form, FormContentWrapper } from 'components/Forms/Form/Form';
import { GiftVouchersExceedPayableAmountWarning } from 'components/Pages/Order/GiftVouchersExceedPayableAmountWarning';
import { TIDs } from 'cypress/tids';
import { TypeProductTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ContactInformationFormContent } from './ContactInformationFormContent';
import { useContactInformationForm, useContactInformationFormMeta } from './contactInformationFormMeta';
import { useContactInformationPageNavigation, useCreateOrder } from './contactInformationUtils';
import { ContactInformationSendOrderButton } from './FormBlocks/ContactInformationSendOrderButton';
import { OrderNotSubmittedAlert } from './OrderNotSubmittedAlert';

export const ContactInformationContent: FC = () => {
    const { t } = useTranslation();
    const [formProviderMethods] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta();
    const { goToPreviousStepFromContactInformationPage } = useContactInformationPageNavigation();
    const { createOrder } = useCreateOrder(formProviderMethods, formMeta);
    const router = useRouter();
    const isOrderRejectedByCartModifications = useSessionStore((s) => s.isOrderRejectedByCartModifications);
    const setIsOrderRejectedByCartModifications = useSessionStore((s) => s.setIsOrderRejectedByCartModifications);
    const { cart } = useCurrentCart();
    const giftVouchersExceedPayableAmount = !!cart?.giftVouchersExceedPayableAmount;
    const cartContainsGiftVoucherProducts = !!cart?.items.some(
        (cartItem) =>
            cartItem.product.productType === TypeProductTypeEnum.ElectronicGiftVoucher ||
            cartItem.product.productType === TypeProductTypeEnum.PrintedGiftVoucher,
    );

    useErrorPopup(formProviderMethods, formMeta.fields, GtmMessageOriginType.contact_information_page);

    // The refusal concerns this page view only. Leaving the page (also by a redirect) forgets it.
    const forgetOrderRejectionOnLeave = () => {
        const forgetOrderRejection = () => setIsOrderRejectedByCartModifications(false);
        router.events.on('routeChangeStart', forgetOrderRejection);

        return () => router.events.off('routeChangeStart', forgetOrderRejection);
    };

    useEffect(forgetOrderRejectionOnLeave, []);

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

                        {isOrderRejectedByCartModifications && <OrderNotSubmittedAlert />}

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
