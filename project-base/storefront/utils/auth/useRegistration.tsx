import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRegistrationByOrderMutation } from 'graphql/requests/registration/mutations/RegistrationByOrderMutation.generated';
import { useRegistrationMutation } from 'graphql/requests/registration/mutations/RegistrationMutation.generated';
import { TypeLoginResult, TypeRegistrationByOrderInput, TypeRegistrationDataInput } from 'graphql/types';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { onGtmSendFormEventHandler } from 'gtm/handlers/onGtmSendFormEventHandler';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { AuthNotification } from 'types/auth';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import { storeAuthNotification } from 'utils/auth/authNotificationStorage';
import { performAuthHardNavigation } from 'utils/auth/performAuthHardNavigation';
import { blurInput } from 'utils/forms/blurInput';

export const useRegistration = () => {
    const [, registerMutation] = useRegistrationMutation();
    const [, registerByOrderMutation] = useRegistrationByOrderMutation();
    const router = useRouter();
    const updateUserEntryState = usePersistStore((s) => s.updateUserEntryState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const domainConfig = useDomainConfig();

    const register = async (registrationInput: Omit<TypeRegistrationDataInput, 'productListsUuids'>) => {
        blurInput();

        const registerResult = await registerMutation(
            {
                input: {
                    cartUuid: registrationInput.cartUuid,
                    city: registrationInput.city,
                    companyCustomer: registrationInput.companyCustomer,
                    companyName: registrationInput.companyName,
                    companyNumber: registrationInput.companyNumber,
                    companyTaxNumber: registrationInput.companyTaxNumber,
                    country: registrationInput.country,
                    email: registrationInput.email,
                    firstName: registrationInput.firstName,
                    lastName: registrationInput.lastName,
                    newsletterSubscription: registrationInput.newsletterSubscription,
                    password: registrationInput.password,
                    postcode: registrationInput.postcode,
                    street: registrationInput.street,
                    telephone: registrationInput.telephone,
                    productListsUuids: Object.values(productListUuids),
                    billingAddressUuid: null,
                },
            },
            { fetch: getAuthMutationFetcher(domainConfig) },
        );

        if (registerResult.data?.Register) {
            return processRegisterResult(registerResult.data.Register);
        }

        return registerResult.error;
    };

    async function processRegisterResult(registerResultData: TypeLoginResult) {
        updateCartUuid(null);
        updateProductListUuids({});

        const authNotification: AuthNotification = registerResultData.showCartMergeInfo
            ? 'registration-with-cart-modifications'
            : 'registration';
        storeAuthNotification(domainConfig.domainId, authNotification);
        updateUserEntryState('registration');
        onGtmSendFormEventHandler(GtmFormType.registration);
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
        if (!(await router.replace('/'))) {
            performAuthHardNavigation('/');
        }

        return undefined;
    }

    const registerByOrder = async (registrationInput: Omit<TypeRegistrationByOrderInput, 'productListsUuids'>) => {
        blurInput();
        const registerResult = await registerByOrderMutation(
            {
                input: {
                    orderUrlHash: registrationInput.orderUrlHash,
                    password: registrationInput.password,
                    productListsUuids: Object.values(productListUuids),
                },
            },
            { fetch: getAuthMutationFetcher(domainConfig) },
        );

        if (registerResult.data?.RegisterByOrder) {
            return processRegisterResult(registerResult.data.RegisterByOrder);
        }

        return registerResult.error;
    };

    return { register, registerByOrder };
};
