'use client';

import { useRouter } from 'next/navigation';
import { usePersistStore } from 'store/usePersistStore';

export const useHandleActionsAfterRegistration = () => {
    const router = useRouter();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);
    // const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const handleActionsAfterRegistration = (showCartMergeInfo: boolean) => {
        updateCartUuid(null);
        updateProductListUuids({});

        updateAuthLoadingState(
            showCartMergeInfo ? 'registration-loading-with-cart-modifications' : 'registration-loading',
        );

        // TODO: loading state
        // updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });

        updateUserEntryState('registration');

        // TODO: gtm
        // onGtmSendFormEventHandler(GtmFormType.registration);

        router.replace('/');
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

    return handleActionsAfterRegistration;
};
