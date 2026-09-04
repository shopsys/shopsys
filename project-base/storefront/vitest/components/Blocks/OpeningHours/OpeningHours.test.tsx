import { render, screen } from '@testing-library/react';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { TypeStoreOpeningStatusEnum } from 'graphql/types';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        lang: 'en',
        t: (key: string) => key,
    }),
}));

vi.mock('utils/formatting/useFormatDate', () => ({
    useFormatDate: () => ({
        formatDate: (date: string) => `formatted(${date})`,
    }),
}));

vi.mock('components/Blocks/OpeningHours/OpeningStatus', () => ({
    OpeningStatus: () => <div>Current opening status</div>,
}));

// a floating week starting on Wednesday 2026-07-15
const openingHours: StoreOrPacketeryPoint['openingHours'] = {
    status: TypeStoreOpeningStatusEnum.Open,
    dayOfWeek: 3,
    openingHoursOfDays: [3, 4, 5, 6, 7, 1, 2].map((dayOfWeek, index) => ({
        date: `2026-07-${15 + index}T00:00:00`,
        dayOfWeek,
        openingHoursRanges: dayOfWeek === 7 ? [] : [{ openingTime: '08:00', closingTime: '18:00' }],
    })),
};

const getRenderedDayNames = (): (string | null | undefined)[] =>
    screen.getAllByRole('listitem').map((listItem) => listItem.querySelector('span')?.textContent?.trim());

describe('OpeningHours', () => {
    test('renders the floating week from today with dates by default', () => {
        render(<OpeningHours openingHours={openingHours} />);

        expect(getRenderedDayNames()[0]).toContain('Today');
        expect(getRenderedDayNames()[0]).toContain('formatted(');
        expect(screen.getByText('Current opening status')).toBeInTheDocument();
    });

    test('renders a standard Monday-Sunday week without dates when a pickup date is given', () => {
        // 2026-07-17 is the Friday of the provided week
        render(<OpeningHours openingHours={openingHours} pickupDate={new Date('2026-07-17T00:00:00')} />);

        expect(getRenderedDayNames()).toEqual([
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ]);
        expect(screen.queryByText(/formatted\(/)).not.toBeInTheDocument();
        expect(screen.queryByText('Current opening status')).not.toBeInTheDocument();

        const fridayRow = screen.getAllByRole('listitem')[4];
        expect(fridayRow.className).toContain('bg-background-accent-less');
    });

    test('highlights no day of the standard week when the pickup date is unknown', () => {
        render(<OpeningHours openingHours={openingHours} pickupDate={null} />);

        expect(getRenderedDayNames()[0]).toBe('Monday');

        screen.getAllByRole('listitem').forEach((listItem) => {
            expect(listItem.className).not.toContain('bg-background-accent-less');
        });
    });
});
