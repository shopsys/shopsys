import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';

export type OpeningHoursOfDay = TypeListedStoreFragment['openingHours']['openingHoursOfDays'][number];

export const isSameLocalDay = (first: Date, second: Date): boolean =>
    first.getFullYear() === second.getFullYear() &&
    first.getMonth() === second.getMonth() &&
    first.getDate() === second.getDate();

export const getIsoDayOfWeek = (date: Date): number => ((date.getDay() + 6) % 7) + 1;

/**
 * Finds the opening hours of the given date; the API provides only the following 7 days,
 * so a farther date falls back to the same day of the week (without the closed days
 * of that specific date)
 */
export const findOpeningHoursOfDayForDate = (
    openingHoursOfDays: OpeningHoursOfDay[],
    date: Date,
): OpeningHoursOfDay | null => {
    const exactDay = openingHoursOfDays.find((openingHoursOfDay) =>
        isSameLocalDay(new Date(openingHoursOfDay.date), date),
    );

    if (exactDay !== undefined) {
        return exactDay;
    }

    const dayOfWeek = getIsoDayOfWeek(date);

    return openingHoursOfDays.find((openingHoursOfDay) => openingHoursOfDay.dayOfWeek === dayOfWeek) ?? null;
};
