import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useLogoutMutation } from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { toast } from 'react-toastify';
import { useLogoutStore } from 'store/useLogoutStore';
import { usePersistStore } from 'store/usePersistStore';
import { getAuthMutationFetcher } from 'utils/auth/authMutationFetcher';
import { storeAuthNotification } from 'utils/auth/authNotificationStorage';
import { performAuthHardNavigation } from 'utils/auth/performAuthHardNavigation';
import { withAuthLock } from 'utils/auth/withAuthLock';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { clearOrderConfirmationContext } from 'utils/order/orderConfirmationContextStorage';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';
import { v4 as uuid } from 'uuid';

export const useLogout = () => {
    const { t } = useTranslation();
    const [, logoutMutation] = useLogoutMutation();

    const resetContactInformation = usePersistStore((s) => s.resetContactInformation);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const domainConfig = useDomainConfig();

    const logout = async () => {
        const { domainId } = domainConfig;
        const { logoutDeadlineByDomain, setLogoutDeadline } = useLogoutStore.getState();
        if (logoutDeadlineByDomain[domainId] !== undefined) {
            return;
        }

        setLogoutDeadline(domainId, Date.now() + 60_000);
        const progressToastId = `logout-${domainId}-${uuid()}`;
        const errorToastId = `${progressToastId}-error`;
        let slowMessageTimeout: ReturnType<typeof setTimeout> | undefined;
        let isLogoutSuccessful = false;
        const retryLogout = () => {
            toast.dismiss(errorToastId);
            void logout();
        };
        const showLogoutError = () => {
            showErrorMessage(t('Unable to log out. Please try again.'), undefined, {
                toastId: errorToastId,
                action: (
                    <Button size="small" variant="tertiary" onClick={retryLogout}>
                        {t('Try again')}
                    </Button>
                ),
            });
        };

        try {
            toast.loading(t<string>('Logging out...'), {
                toastId: progressToastId,
                role: 'status',
                closeButton: false,
                closeOnClick: false,
                draggable: false,
                autoClose: false,
            });
            slowMessageTimeout = setTimeout(() => {
                toast.update(progressToastId, {
                    render: t<string>('Logging out is taking longer than usual. Please wait.'),
                });
            }, 10_000);
            const logoutResult = await logoutMutation(
                {},
                {
                    // URQL may need to refresh first; lock only the request that clears the cookies.
                    fetch: (input, init) =>
                        withAuthLock(domainId, (signal) =>
                            getAuthMutationFetcher(domainConfig)(input, { ...init, signal }),
                        ),
                },
            );

            if (logoutResult.data?.Logout) {
                resetContactInformation();
                clearOrderConfirmationContext();
                updateProductListUuids({});
                storeAuthNotification(domainId, 'logout');
                dispatchBroadcastChannel('reloadPage', domainId);
                isLogoutSuccessful = true;
                performAuthHardNavigation();
            } else {
                showLogoutError();
            }
        } catch {
            showLogoutError();
        } finally {
            clearTimeout(slowMessageTimeout);
            toast.dismiss(progressToastId);
            if (!isLogoutSuccessful) {
                setLogoutDeadline(domainId, undefined);
            }
        }
    };

    return logout;
};
