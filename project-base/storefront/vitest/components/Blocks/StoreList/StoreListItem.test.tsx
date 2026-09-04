import { fireEvent, render, screen } from '@testing-library/react';
import { StoreListItem } from 'components/Blocks/StoreList/StoreListItem';
import { TIDs } from 'cypress/tids';
import { TypeStoreOpeningStatusEnum } from 'graphql/types';
import { type ReactNode } from 'react';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        lang: 'en',
        t: (key: string, options?: Record<string, string>) =>
            key.replace('{{storeName}}', options?.storeName ?? '').trim(),
    }),
}));

vi.mock('components/Blocks/OpeningHours/OpeningHours', () => ({
    OpeningHours: () => <div>Store opening hours detail</div>,
}));

vi.mock('components/Blocks/OpeningHours/OpeningHoursToday', () => ({
    default: () => <div>7 AM - 5 PM</div>,
}));

vi.mock('components/Blocks/OpeningHours/OpeningStatus', () => ({
    OpeningStatus: () => <div>Opening soon</div>,
}));

vi.mock('components/Blocks/ExpectedDeliveryDateInfo/ExpectedDeliveryDateInfo', () => ({
    ExpectedDeliveryDateInfo: () => <div>Expected delivery date</div>,
}));

vi.mock('components/Blocks/OpeningHours/OpeningHoursOfPickupDay', () => ({
    OpeningHoursOfPickupDay: () => <div>Pickup day opening hours</div>,
}));

vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({ children }: { children: ReactNode }) => <a href="/store-detail">{children}</a>,
}));

const toLocalDateString = (date: Date): string => {
    const pad = (value: number) => String(value).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T00:00:00`;
};

const today = new Date();
const tomorrow = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);

const store: StoreOrPacketeryPoint = {
    __typename: 'Store',
    identifier: 'store-uuid',
    slug: '/store-detail',
    name: 'Test store',
    street: 'Test street',
    postcode: '12345',
    city: 'Test city',
    distance: null,
    description: null,
    email: 'test@example.com',
    phone: '+420 123 456 789',
    specialMessage: null,
    latitude: '50',
    longitude: '14',
    country: {
        __typename: 'Country',
        name: 'Czechia',
        code: 'CZ',
    },
    mainImage: null,
    openingHours: {
        status: TypeStoreOpeningStatusEnum.OpenSoon,
        dayOfWeek: 1,
        openingHoursOfDays: [
            {
                date: toLocalDateString(today),
                dayOfWeek: 1,
                openingHoursRanges: [
                    {
                        openingTime: '07:00',
                        closingTime: '17:00',
                    },
                ],
            },
            {
                date: toLocalDateString(tomorrow),
                dayOfWeek: 2,
                openingHoursRanges: [
                    {
                        openingTime: '08:00',
                        closingTime: '16:00',
                    },
                ],
            },
        ],
    },
};

describe('StoreListItem', () => {
    test('expands details from the store summary in default mode', () => {
        const { container } = render(
            <StoreListItem isDistanceFromSearchText={false} isSelected={false} store={store} />,
        );
        const storeCard = container.querySelector(`[data-tid="${TIDs.store_list_item_}store-uuid"]`)!;
        const storeSummary = storeCard.querySelector('[role="button"]')!;

        fireEvent.click(storeSummary);

        expect(screen.getByText('Store opening hours detail')).toBeInTheDocument();
        expect(storeSummary).toHaveAttribute('aria-expanded', 'true');
    });

    test.each(['Enter', ' '])('expands details from the store summary with the %s key', (key) => {
        const { container } = render(
            <StoreListItem isDistanceFromSearchText={false} isSelected={false} store={store} />,
        );
        const storeCard = container.querySelector(`[data-tid="${TIDs.store_list_item_}store-uuid"]`)!;
        const storeSummary = storeCard.querySelector('[role="button"]')!;

        fireEvent.keyDown(storeSummary, { key });

        expect(screen.getByText('Store opening hours detail')).toBeInTheDocument();
        expect(storeSummary).toHaveAttribute('aria-expanded', 'true');
    });

    test('selects store from the card body and expands details only from the chevron in pickup selection mode', () => {
        const onSelectStoreCallback = vi.fn();
        render(
            <StoreListItem
                isDistanceFromSearchText={false}
                isSelected={false}
                mode="selectOnItemClick"
                store={store}
                onSelectStoreCallback={onSelectStoreCallback}
            />,
        );

        fireEvent.click(screen.getByRole('radio', { name: 'Select store Test store' }));

        expect(onSelectStoreCallback).toHaveBeenCalledWith('store-uuid');
        expect(screen.queryByText('Store opening hours detail')).not.toBeInTheDocument();
        expect(screen.queryByText('Select store')).not.toBeInTheDocument();

        const storeInfoToggle = screen.getByRole('button', { name: 'Expand store info Test store' });
        fireEvent.click(storeInfoToggle);

        expect(screen.getByText('Store opening hours detail')).toBeInTheDocument();
        expect(storeInfoToggle).toHaveAttribute('aria-expanded', 'true');
    });

    test('marks the selected store with brand border', () => {
        const { container } = render(
            <StoreListItem
                isDistanceFromSearchText={false}
                isSelected
                mode="selectOnItemClick"
                store={store}
                onSelectStoreCallback={vi.fn()}
            />,
        );

        expect(container.querySelector(`[data-tid="${TIDs.store_list_item_}store-uuid"]`)).toHaveClass(
            'border-border-brand',
        );
        expect(screen.getByRole('radio', { name: 'Select store Test store' })).toHaveAttribute('aria-checked', 'true');
    });

    test('shows the current opening status when the pickup is today', () => {
        render(
            <StoreListItem
                isDistanceFromSearchText={false}
                isSelected={false}
                store={{ ...store, expectedDeliveryDate: toLocalDateString(today) }}
            />,
        );

        expect(screen.getByText('Opening soon')).toBeInTheDocument();
        expect(screen.queryByText('Pickup day opening hours')).not.toBeInTheDocument();
    });

    test('shows the opening hours of the pickup day instead of the current status for another day', () => {
        render(
            <StoreListItem
                isDistanceFromSearchText={false}
                isSelected={false}
                store={{ ...store, expectedDeliveryDate: toLocalDateString(tomorrow) }}
            />,
        );

        expect(screen.queryByText('Opening soon')).not.toBeInTheDocument();
        expect(screen.queryByText('7 AM - 5 PM')).not.toBeInTheDocument();
        expect(screen.getByText('Pickup day opening hours')).toBeInTheDocument();
    });

    test('shows neither the status nor the hours when the pickup date is unknown', () => {
        render(
            <StoreListItem
                isDistanceFromSearchText={false}
                isSelected={false}
                store={{ ...store, expectedDeliveryDate: null }}
            />,
        );

        expect(screen.queryByText('Opening soon')).not.toBeInTheDocument();
        expect(screen.queryByText('7 AM - 5 PM')).not.toBeInTheDocument();
        expect(screen.queryByText('Pickup day opening hours')).not.toBeInTheDocument();
    });

    test('keeps the current opening status on the stores page without a pickup date', () => {
        render(<StoreListItem isDistanceFromSearchText={false} isSelected={false} store={store} />);

        expect(screen.getByText('Opening soon')).toBeInTheDocument();
        expect(screen.getByText('7 AM - 5 PM')).toBeInTheDocument();
    });

    test('keeps the store info toggle appearance consistent when expanded', () => {
        const { container } = render(
            <StoreListItem isDistanceFromSearchText={false} isSelected={false} store={store} />,
        );
        const collapsedToggle = container.querySelector<HTMLButtonElement>(
            'button[aria-label="Expand store info Test store"]',
        )!;

        expect(collapsedToggle).toHaveClass('size-8', 'rounded-md');
        expect(collapsedToggle).not.toHaveClass('rounded-none');

        fireEvent.click(collapsedToggle);

        const expandedToggle = container.querySelector<HTMLButtonElement>(
            'button[aria-label="Collapse store info Test store"]',
        )!;
        expect(expandedToggle).toHaveClass('size-8', 'rounded-md');
        expect(expandedToggle).not.toHaveClass('rounded-none');
    });
});
