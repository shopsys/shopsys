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

vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({ children }: { children: ReactNode }) => <a href="/store-detail">{children}</a>,
}));

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
                date: '2026-07-07',
                dayOfWeek: 1,
                openingHoursRanges: [
                    {
                        openingTime: '07:00',
                        closingTime: '17:00',
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
});
