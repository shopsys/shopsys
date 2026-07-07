import { createIntlDateTimeFormatter } from 'utils/formaters/createIntlDateTimeFormatter';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isDateQueryValid } from 'utils/parsing/getDateTimeFromDateQuery';

export const datePickerClassNames = {
    root: 'font-secondary text-input-text-default',
    months: 'flex',
    month: 'space-y-3',
    month_caption: 'flex items-center justify-center px-9',
    caption_label: 'font-semibold text-sm',
    dropdowns: 'flex items-center gap-2',
    dropdown_root: 'relative',
    dropdown:
        'cursor-pointer rounded-md border border-input-border-default bg-input-bg-default px-2 py-1 pr-7 font-semibold text-sm outline-hidden focus:border-input-border-active',
    nav: 'absolute inset-x-3 top-3 flex items-center justify-between',
    button_previous:
        'inline-flex size-8 cursor-pointer items-center justify-center rounded-button text-icon-default transition hover:bg-fill-accent-less hover:text-icon-accent disabled:pointer-events-none disabled:opacity-40',
    button_next:
        'inline-flex size-8 cursor-pointer items-center justify-center rounded-button text-icon-default transition hover:bg-fill-accent-less hover:text-icon-accent disabled:pointer-events-none disabled:opacity-40',
    month_grid: 'w-full border-collapse',
    weekdays: 'flex',
    weekday: 'flex size-9 items-center justify-center font-semibold text-input-placeholder-default text-xs',
    week: 'mt-1 flex',
    day: 'size-9 p-0 text-center',
    day_button:
        'flex size-9 cursor-pointer items-center justify-center rounded-button font-semibold text-sm transition hover:bg-fill-accent-less hover:text-link-hovered focus:bg-fill-accent-less focus:outline-hidden',
    selected:
        '[&_button]:bg-link-default [&_button]:text-text-inverted [&_button]:hover:bg-link-hovered [&_button]:hover:text-text-inverted',
    today: '[&_button]:ring-2 [&_button]:ring-link-default [&_button]:ring-inset',
    outside: 'text-input-placeholder-default opacity-50',
    disabled: 'pointer-events-none opacity-40',
};

export const parseDatePickerValue = (value: string): Date | undefined => {
    if (!isDateQueryValid(value)) {
        return undefined;
    }

    const [, year, month, day] = value.match(/^(\d{4})-(\d{2})-(\d{2})$/) ?? [];
    const date = new Date(Number(year), Number(month) - 1, Number(day));

    return Number.isNaN(date.getTime()) ? undefined : date;
};

export const formatDatePickerValue = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

export const formatDisplayDate = (date: Date, lang: string): string =>
    createIntlDateTimeFormatter(
        {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
        },
        undefined,
        lang,
    ).format(date);

export const getDatePickerFormatters = (t: ReturnType<typeof useTranslation>['t']) => ({
    formatCaption: (month: Date): string =>
        `${t(datePickerMonthTranslationKeys[month.getMonth()])} ${month.getFullYear()}`,
    formatDay: (date: Date): string => String(date.getDate()),
    formatWeekdayName: (weekday: Date): string => t(datePickerWeekdayShortTranslationKeys[weekday.getDay()]),
});

export const getDatePickerLabels = (t: ReturnType<typeof useTranslation>['t'], lang: string) => ({
    labelDayButton: (date: Date): string =>
        t('Choose {{ date }}', {
            date: formatDisplayDate(date, lang),
        }),
    labelGrid: (date: Date): string => `${t(datePickerMonthTranslationKeys[date.getMonth()])} ${date.getFullYear()}`,
    labelNext: (): string => t('Next month'),
    labelPrevious: (): string => t('Previous month'),
    labelWeekday: (date: Date): string => t(datePickerWeekdayTranslationKeys[date.getDay()]),
});

const datePickerMonthTranslationKeys = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
] as const;

const datePickerWeekdayTranslationKeys = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
] as const;

const datePickerWeekdayShortTranslationKeys = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as const;
