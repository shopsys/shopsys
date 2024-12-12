'use client';

import { useHandleActionsAfterRegistration } from './useHandleActionsAfterRegistration';
import { registrationAction } from 'app/_actions/registrationAction';
import { RegistrationFormMetaType } from 'app/_components/Blocks/Registration/registrationFormMeta';
import { TypeRegistrationMutationVariables } from 'graphql/requests/registration/mutations/RegistrationMutation.ssr';
import useTranslation from 'next-translate/useTranslation';
import { SubmitHandler, useFormContext } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { RegistrationFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { handleFormErrors } from 'utils/forms/handleFormErrors';

type UseRegistrationProps = {
    formMeta: RegistrationFormMetaType;
};

export const useRegistration = ({ formMeta }: UseRegistrationProps) => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);

    const formProviderMethods = useFormContext<RegistrationFormType>();

    const handleActionsAfterRegistration = useHandleActionsAfterRegistration();

    const handleRegistration: SubmitHandler<RegistrationFormType> = async (formData) => {
        blurInput();

        const registrationData: TypeRegistrationMutationVariables = {
            input: {
                email: formData.email,
                firstName: formData.firstName,
                lastName: formData.lastName,
                telephone: formData.telephone,
                password: formData.password,
                companyCustomer: formData.customer === 'companyCustomer',
                companyName: formData.companyName,
                companyNumber: formData.companyNumber,
                companyTaxNumber: formData.companyTaxNumber,
                street: formData.street,
                city: formData.city,
                country: formData.country.value,
                postcode: formData.postcode,
                newsletterSubscription: formData.newsletterSubscription,
                cartUuid: cartUuid,
                billingAddressUuid: null,
                productListsUuids: Object.values(productListUuids),
            },
        };

        const { error, showCartMergeInfo } = await registrationAction(registrationData);

        if (error) {
            handleFormErrors(error, formProviderMethods, t, formMeta.messages.error);
            return;
        }

        handleActionsAfterRegistration(showCartMergeInfo);

        // TODO: maybe its not necessary to clear the form here
        // clearForm(error, formProviderMethods, defaultValues);
    };

    // TODO: will be used in the future as server action
    // const registerByOrder = async (registrationInput: Omit<TypeRegistrationByOrderInput, 'productListsUuids'>) => {
    //     blurInput();
    //     const registerResult = await registerByOrderMutation({
    //         input: {
    //             orderUrlHash: registrationInput.orderUrlHash,
    //             password: registrationInput.password,
    //             productListsUuids: Object.values(productListUuids),
    //         },
    //     });

    //     if (registerResult.data?.RegisterByOrder) {
    //         return processRegisterResult(registerResult.data.RegisterByOrder);
    //     }

    //     return registerResult.error;
    // };

    return handleRegistration;
};
