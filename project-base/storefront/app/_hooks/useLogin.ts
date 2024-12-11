'use client';

import { useHandleActionsAfterLogin } from './useHandleActionsAfterLogin';
import { loginAction } from 'app/_actions/loginAction';
import { TypeLoginMutationVariables } from 'graphql/requests/auth/mutations/LoginMutation.ssr';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { SubmitHandler, useFormContext } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { LoginFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { handleFormErrors } from 'utils/forms/handleFormErrors';

type UseLoginProps = {
    shouldOverwriteCustomerUserCart?: boolean;
};

export const useLogin = ({ shouldOverwriteCustomerUserCart }: UseLoginProps) => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);

    const formProviderMethods = useFormContext<LoginFormType>();

    const handleActionsAfterLogin = useHandleActionsAfterLogin();

    const handleLogin: SubmitHandler<LoginFormType> = async (formData) => {
        blurInput();

        const loginData: TypeLoginMutationVariables = {
            email: formData.email,
            password: formData.password,
            previousCartUuid: cartUuid,
            productListsUuids: Object.values(productListUuids),
            shouldOverwriteCustomerUserCart,
        };

        const { error, showCartMergeInfo } = await loginAction(loginData);

        if (error) {
            handleFormErrors(error, formProviderMethods, t, undefined, undefined, GtmMessageOriginType.login_popup);
            return;
        }

        handleActionsAfterLogin(showCartMergeInfo, undefined);
    };

    return handleLogin;
};
