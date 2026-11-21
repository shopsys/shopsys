import { render, screen } from '@testing-library/react';
import { CustomerDashboard } from 'components/Pages/Customer/CustomerDashboard';
import { AuthorizationProvider } from 'components/providers/AuthorizationProvider';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { TypeComplaintStatusEnum, TypeCustomerUserRoleEnum, TypeOrderStatusEnum } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestConfig } from 'vitest/helpers/mockPublicConfig';

const queries = vi.hoisted(() => ({ orders: vi.fn(), complaints: vi.fn() }));
vi.mock('graphql/requests/orders/queries/DashboardOrdersQuery.generated', () => ({
    useDashboardOrdersQuery: queries.orders,
}));
vi.mock('graphql/requests/complaints/queries/DashboardComplaintsQuery.generated', () => ({
    useDashboardComplaintsQuery: queries.complaints,
}));
vi.mock('connectors/customer/CurrentCustomer', () => ({
    useCurrentCustomerData: () => ({ uuid: 'customer', companyCustomer: true }),
}));
vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({ useSettingsQuery: () => [{}] }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));

const renderDashboard = (domainIndex: number, roles: TypeCustomerUserRoleEnum[]) =>
    render(
        <DomainConfigProvider domainConfig={defaultTestConfig.domains[domainIndex]}>
            <AuthorizationProvider customerUserRoles={roles}>
                <CustomerDashboard title="Account overview" />
            </AuthorizationProvider>
        </DomainConfigProvider>,
    );

describe('CustomerDashboard', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        queries.orders.mockReturnValue([
            {
                data: {
                    orders: { edges: [{ node: { number: 'O123', date: '2026-05-01', status: 'New' } }] },
                    orderStatusCounts: [
                        { status: { type: TypeOrderStatusEnum.New }, count: 2 },
                        { status: { type: TypeOrderStatusEnum.InProgress }, count: 4 },
                        { status: { type: TypeOrderStatusEnum.Done }, count: 7 },
                    ],
                },
            },
        ]);
        queries.complaints.mockReturnValue([
            {
                data: {
                    complaints: { edges: [{ node: { number: 'C123', date: '2026-05-03', status: 'Resolved' } }] },
                    complaintStatusCounts: [
                        { status: { type: TypeComplaintStatusEnum.New }, count: 3 },
                        { status: { type: TypeComplaintStatusEnum.Resolved }, count: 5 },
                    ],
                },
            },
        ]);
    });

    test('shows recent records and counts open cases across the full history', () => {
        renderDashboard(0, []);
        expect(screen.getByText('Open orders:')).toHaveTextContent('Open orders: 6');
        expect(screen.getByText('Open complaints:')).toHaveTextContent('Open complaints: 3');
        expect(screen.getByText('O123')).toHaveAttribute('href', '/customer/order-detail?orderNumber=O123');
        expect(screen.getByText('C123')).toHaveAttribute('href', '/customer/complaint-detail?complaintNumber=C123');
        expect(screen.queryByText('Customer users')).not.toBeInTheDocument();
    });

    test('only requests and links the areas accessible to a company user', () => {
        renderDashboard(1, [
            TypeCustomerUserRoleEnum.RoleApiCompanyOrdersView,
            TypeCustomerUserRoleEnum.RoleApiManageCustomers,
        ]);
        expect(queries.orders).toHaveBeenCalledWith(expect.objectContaining({ pause: false }));
        expect(queries.complaints).toHaveBeenCalledWith(expect.objectContaining({ pause: true }));
        expect(screen.queryByText('Recent complaints')).not.toBeInTheDocument();
        expect(screen.getByText('Customer users')).toHaveAttribute('href', '/zakaznik/uzivatele');
        expect(screen.getByText('O123')).toHaveAttribute('href', '/zakaznik/detail-objednavky?orderNumber=O123');
    });

    test('keeps the other section available when one request fails', () => {
        queries.orders.mockReturnValue([{ error: new Error('Orders unavailable') }]);
        queries.complaints.mockReturnValue([{ data: { complaints: { edges: [] }, complaintStatusCounts: [] } }]);
        renderDashboard(0, []);
        expect(screen.getByRole('alert')).toHaveTextContent('Something went wrong.');
        expect(screen.getByText('You have no complaints')).toBeInTheDocument();
        expect(screen.getByText('Open complaints:')).toHaveTextContent('Open complaints: 0');
    });
});
