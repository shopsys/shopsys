import { LockCheckIcon } from 'components/Basic/Icon/LockCheckIcon';
import { LockCrossIcon } from 'components/Basic/Icon/LockCrossIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRecoverPasswordMutation } from 'graphql/requests/passwordRecovery/mutations/RecoverPasswordMutation.generated';
import { FormProvider, SubmitHandler, useController } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { NewPasswordFormType } from 'types/form';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import { useLoginAfterPasswordRecovery } from 'utils/auth/useLogin';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useRecoveryPasswordForm, useRecoveryPasswordFormMeta } from './recoveryPasswordFormMeta';

type NewPasswordContentProps = {
    hash: string;
    email: string;
};

export const NewPasswordContent: FC<NewPasswordContentProps> = ({ email, hash }) => {
    const { t } = useTranslation();
    const domainConfig = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const [, newPassword] = useRecoverPasswordMutation();
    const handleActionsAfterPasswordRecovery = useLoginAfterPasswordRecovery();
    const { url } = domainConfig;
    const [resetPasswordUrl] = getInternationalizedStaticUrls(['/reset-password'], url);
    const [formProviderMethods] = useRecoveryPasswordForm();
    const formMeta = useRecoveryPasswordFormMeta();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });
    const {
        fieldState: { error },
        field: { value: newPasswordValue },
    } = useController({ name: formMeta.fields.newPasswordConfirm.name, control: formProviderMethods.control });

    const onNewPasswordHandler: SubmitHandler<NewPasswordFormType> = async (newPasswordFormData) => {
        const newPasswordResult = await newPassword(
            {
                hash,
                email,
                newPassword: newPasswordFormData.newPassword,
                cartUuid: cartUuid ?? undefined,
                productListsUuids: Object.values(productListUuids),
            },
            { fetch: getAuthMutationFetcher(domainConfig) },
        );

        const recoverPasswordData = newPasswordResult.data?.RecoverPassword;

        if (recoverPasswordData) {
            showSuccessMessage(formMeta.messages.success);

            handleActionsAfterPasswordRecovery(recoverPasswordData.showCartMergeInfo);
        }

        handleError(newPasswordResult.error);
    };

    if (hash === '' || email === '') {
        return (
            <Webline width="lg">
                <VerticalStack gap="sm">
                    <PageHero
                        actionHref={resetPasswordUrl}
                        actionSkeletonType="reset-password"
                        actionTitle={t('Resend new password recovery link')}
                        description={t('Unable to load form data. Request a new link to set your password again.')}
                        icon={LockCrossIcon}
                        title={t('Set new password')}
                    />
                </VerticalStack>
            </Webline>
        );
    }

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                <PageHero
                    icon={LockCheckIcon}
                    title={t('Set new password')}
                    description={t(
                        'Set new password form for setting your new password. Please fill in your new password and confirm it.',
                    )}
                />
                <FormProvider {...formProviderMethods}>
                    <Form
                        className="flex w-full justify-center"
                        formName={formMeta.formName}
                        onSubmit={formProviderMethods.handleSubmit(onNewPasswordHandler)}
                    >
                        <FormContentWrapper>
                            <FormBlockWrapper>
                                <PasswordInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.newPassword.name}
                                    passwordInputProps={{
                                        label: formMeta.fields.newPassword.label,
                                    }}
                                />

                                <PasswordInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.newPasswordConfirm.name}
                                    passwordInputProps={{
                                        label: formMeta.fields.newPasswordConfirm.label,
                                    }}
                                />
                            </FormBlockWrapper>

                            <FormButtonWrapper>
                                <SubmitButton hasDisabledCursor={!!error || newPasswordValue.length === 0}>
                                    {t('Set new password')}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormContentWrapper>
                    </Form>
                </FormProvider>
            </VerticalStack>
        </Webline>
    );
};
