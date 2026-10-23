import {
    formatDatePickerValue,
    getDatePickerFormatters,
    getDatePickerLabels,
    parseDatePickerValue,
} from 'components/Forms/DatePicker/datePickerUtils';
import { type Translate, type TranslationQuery } from 'next-translate';
import cs from 'public/locales/cs/common.json';
import en from 'public/locales/en/common.json';
import sk from 'public/locales/sk/common.json';
import { describe, expect, test } from 'vitest';

const translate: Translate = <T = string>(key: string | TemplateStringsArray, options?: TranslationQuery | null): T => {
    const date = (options as { date?: string } | null | undefined)?.date;

    return (date !== undefined ? `${key.toString()} ${date}` : key.toString()) as T;
};

describe('datePickerUtils tests', () => {
    test.each([
        {
            lang: 'cs',
            catalog: cs,
            months: [
                'Leden',
                'Únor',
                'Březen',
                'Duben',
                'Květen',
                'Červen',
                'Červenec',
                'Srpen',
                'Září',
                'Říjen',
                'Listopad',
                'Prosinec',
            ],
            weekdays: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
        },
        {
            lang: 'sk',
            catalog: sk,
            months: [
                'Január',
                'Február',
                'Marec',
                'Apríl',
                'Máj',
                'Jún',
                'Júl',
                'August',
                'September',
                'Október',
                'November',
                'December',
            ],
            weekdays: ['Ne', 'Po', 'Ut', 'St', 'Št', 'Pi', 'So'],
        },
        {
            lang: 'en',
            catalog: en,
            months: [
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
            ],
            weekdays: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        },
    ])(
        'localizes every month and weekday abbreviation using the $lang catalog',
        ({ lang, catalog, months, weekdays }) => {
            const t: Translate = <T = string>(key: string | TemplateStringsArray): T =>
                (catalog as Record<string, string>)[key.toString()] as T;
            const formatters = getDatePickerFormatters(t);
            const labels = getDatePickerLabels(t, lang);

            months.forEach((month, index) => {
                const date = new Date(2026, index, 1);
                expect(formatters.formatCaption(date)).toBe(`${month} 2026`);
                expect(labels.labelGrid(date)).toBe(`${month} 2026`);
            });
            weekdays.forEach((weekday, index) => {
                expect(formatters.formatWeekdayName(new Date(2026, 5, 7 + index))).toBe(weekday);
            });
        },
    );

    test('valid date picker value should be parsed as local date', () => {
        const parsedDate = parseDatePickerValue('2026-02-28');

        expect(parsedDate?.getFullYear()).toBe(2026);
        expect(parsedDate?.getMonth()).toBe(1);
        expect(parsedDate?.getDate()).toBe(28);
    });

    test('impossible date picker value should be ignored', () => {
        expect(parseDatePickerValue('2026-02-31')).toBeUndefined();
    });

    test('date should be formatted for URL query value', () => {
        expect(formatDatePickerValue(new Date(2026, 1, 3))).toBe('2026-02-03');
    });

    test('date picker formatters should use project translation keys', () => {
        const formatters = getDatePickerFormatters(translate);

        expect(formatters.formatCaption(new Date(2026, 5, 1))).toBe('June 2026');
        expect(formatters.formatDay(new Date(2026, 5, 9))).toBe('9');
        expect(formatters.formatWeekdayName(new Date(2026, 5, 9))).toBe('Tu');
    });

    test('date picker labels should use translated texts', () => {
        const labels = getDatePickerLabels(translate, 'en');

        expect(labels.labelGrid(new Date(2026, 5, 1))).toBe('June 2026');
        expect(labels.labelNext()).toBe('Next month');
        expect(labels.labelPrevious()).toBe('Previous month');
        expect(labels.labelWeekday(new Date(2026, 5, 9))).toBe('Tuesday');
        expect(labels.labelDayButton(new Date(2026, 5, 9))).toBe('Choose {{ date }} 6/9/2026');
    });
});
