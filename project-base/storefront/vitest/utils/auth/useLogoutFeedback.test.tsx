import { act, fireEvent, render, screen, waitFor } from '@testing-library/react';
import { ToastContainerWrapper } from 'components/Pages/App/ToastContainerWrapper';
import { toast } from 'react-toastify';
import { useLogoutStore } from 'store/useLogoutStore';
import { useLogout } from 'utils/auth/useLogout';
import { afterEach, expect, test, vi } from 'vitest';

const { logoutMutation, navigate } = vi.hoisted(() => ({ logoutMutation: vi.fn(), navigate: vi.fn() }));
vi.mock('components/providers/DomainConfigProvider', () => ({ useDomainConfig: () => ({ domainId: 1 }) }));
vi.mock('graphql/requests/auth/mutations/LogoutMutation.generated', () => ({
    useLogoutMutation: () => [{}, logoutMutation],
}));
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: unknown) => unknown) =>
        selector({ resetContactInformation: vi.fn(), updateProductListUuids: vi.fn() }),
}));
vi.mock('utils/auth/performAuthHardNavigation', () => ({ performAuthHardNavigation: navigate }));
vi.mock('utils/auth/authNotificationStorage', () => ({ storeAuthNotification: vi.fn() }));
vi.mock('utils/order/orderConfirmationContextStorage', () => ({ clearOrderConfirmationContext: vi.fn() }));
vi.mock('utils/useBroadcastChannel', () => ({ dispatchBroadcastChannel: vi.fn() }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (message: string) => message }) }));

const LogoutControl = () => {
    const logout = useLogout();
    return <button onClick={() => void logout()}>Logout</button>;
};

afterEach(() => {
    act(() => toast.dismiss());
    useLogoutStore.setState({ logoutDeadlineByDomain: {} });
});

test('a second immediately failed logout still offers a visible retry action during toast exit', async () => {
    logoutMutation.mockResolvedValue({ data: { Logout: false } });
    render(
        <>
            <ToastContainerWrapper />
            <LogoutControl />
        </>,
    );
    fireEvent.click(screen.getByRole('button', { name: 'Logout' }));
    const retry = await screen.findByRole('button', { name: 'Try again' });
    expect(screen.getByRole('alert')).toHaveTextContent('Unable to log out. Please try again.');

    fireEvent.click(retry);

    await waitFor(() => expect(logoutMutation).toHaveBeenCalledTimes(2));
    await waitFor(() => {
        const activeErrors = Array.from(document.querySelectorAll('.Toastify__toast[data-in="true"]')).filter(
            (element) => element.textContent?.includes('Unable to log out. Please try again.'),
        );
        expect(activeErrors).toHaveLength(1);
        expect(activeErrors[0].querySelector('button')).toHaveTextContent('Try again');
    });
    expect(navigate).not.toHaveBeenCalled();
    let finishLogout: (value: { data: { Logout: boolean } }) => void = () => {};
    logoutMutation.mockReturnValueOnce(
        new Promise((resolve) => {
            finishLogout = resolve;
        }),
    );
    const activeRetry = document.querySelector('.Toastify__toast[data-in="true"] button');
    expect(activeRetry).not.toBeNull();
    fireEvent.click(activeRetry!);
    await waitFor(() => {
        const activeProgress = Array.from(document.querySelectorAll('.Toastify__toast[data-in="true"]')).filter(
            (element) => element.textContent?.includes('Logging out...'),
        );
        expect(activeProgress).toHaveLength(1);
    });
    await act(async () => {
        finishLogout({ data: { Logout: true } });
    });
    expect(navigate).toHaveBeenCalledOnce();
});
