import { render, screen } from '@testing-library/react';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { useCurrentCustomerDataMock } = vi.hoisted(() => ({
    useCurrentCustomerDataMock: vi.fn(),
}));

vi.mock('connectors/customer/CurrentCustomer', () => ({
    useCurrentCustomerData: useCurrentCustomerDataMock,
}));

vi.mock('utils/useNotificationBarRevalidation', () => ({
    useNotificationBarsWithRevalidation: () => ({ activeNotificationBars: [] }),
}));

vi.mock('utils/auth/useLogout', () => ({
    useLogout: () => vi.fn(),
}));

vi.mock('next-translate/Trans', () => ({
    default: ({ values }: { values: { email: string } }) => <span>{`Logged in as ${values.email}`}</span>,
}));

const CUSTOMER_EMAIL = 'customer@example.com';

describe('NotificationBars', () => {
    beforeEach(() => {
        useCurrentCustomerDataMock.mockReset();
    });

    test('warns the administrator logged in as a customer', () => {
        useCurrentCustomerDataMock.mockReturnValue({ email: CUSTOMER_EMAIL, isAdministratorLoggedAsCustomer: true });

        render(<NotificationBars />);

        expect(screen.getByText(`Logged in as ${CUSTOMER_EMAIL}`)).toBeInTheDocument();
    });

    test('does not warn a customer logged in by themselves', () => {
        useCurrentCustomerDataMock.mockReturnValue({ email: CUSTOMER_EMAIL, isAdministratorLoggedAsCustomer: false });

        render(<NotificationBars />);

        expect(screen.queryByText(`Logged in as ${CUSTOMER_EMAIL}`)).not.toBeInTheDocument();
    });
});
