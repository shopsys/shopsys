import Register from '../utils/Register';
import 'flatpickr';

export default function datePicker($container) {
    const htmlLang = document.documentElement.lang || 'en';
    const currentLang = htmlLang.split('-')[0].toLowerCase();

    const localeMap = {
        en: null,
        // add specific mappings (e.g. 'in: id' for legacy code for Indonesian)
    };

    $container.filterAllNodes('[data-js-datepicker]').each(function () {
        const $element = $(this);
        const defaultOptions = {
            dateFormat: 'd.m.Y', // backend accepted format
            monthSelectorType: 'static',
            altInput: true,
            altFormat: 'm/d/Y', // default English format
            allowInput: true,
            onReady: rememberLastDispatchedValue,
            onChange: rememberLastDispatchedValue,
            onClose: dispatchChangeAfterSilentValueCommit,
        };

        const locale = localeMap[currentLang] || currentLang;

        if (!locale) {
            $element.flatpickr(defaultOptions);

            return;
        }

        import(`flatpickr/dist/l10n/${locale}.js`)
            .then(() => {
                $element.flatpickr({
                    ...defaultOptions,
                    locale,
                    altFormat: getDateFormatForLocale(locale),
                });
            })
            .catch(() => {
                $element.flatpickr(defaultOptions);
            });
    });
}

function rememberLastDispatchedValue(_selectedDates, _dateStr, instance) {
    instance.input.dataset.lastDispatchedValue = instance.input.value;
}

function dispatchChangeAfterSilentValueCommit(selectedDates, dateStr, instance) {
    if (instance.input.value === instance.input.dataset.lastDispatchedValue) {
        return;
    }

    rememberLastDispatchedValue(selectedDates, dateStr, instance);
    instance.input.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
}

function getDateFormatForLocale(locale) {
    // Pick a fixed sample date (1999-12-31) so parts are predictable
    const parts = new Intl.DateTimeFormat(locale).formatToParts(new Date(1999, 11, 31));

    // Map Intl part types to Flatpickr tokens
    const tokenMap = { year: 'Y', month: 'm', day: 'd' };

    // Rebuild the pattern
    return parts
        .map(p =>
            p.type === 'literal' // separator, like "/" or "."
                ? p.value
                : tokenMap[p.type],
        )
        .join('');
}

new Register().registerCallback(datePicker, 'datePicker');
