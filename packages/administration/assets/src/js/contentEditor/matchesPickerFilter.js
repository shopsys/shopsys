const COMBINING_MARKS = /[̀-ͯ]/g;

function normalize(text) {
    return text.normalize('NFD').replace(COMBINING_MARKS, '').toLowerCase();
}

/**
 * Whether a block of the picker is shown for the typed filter, case and diacritics do not matter.
 *
 * @param {string} label
 * @param {string} query
 * @returns {boolean}
 */
export default function matchesPickerFilter(label, query) {
    return normalize(label).includes(normalize(query.trim()));
}
