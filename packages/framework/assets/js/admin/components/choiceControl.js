import $ from 'jquery';
export function select(choiceListSelector, value) {
    const $choiceList = $($(choiceListSelector));

    const choice = findChoice($choiceList, value);
    const $choice = $(choice);
    if ($choice.is('input')) {
        $choice.prop('checked', true);
    } else if ($choice.is('option')) {
        $choice.prop('selected', true);
    }
}

export function deselect(choiceListSelector, value) {
    const $choiceList = $($(choiceListSelector));

    const choice = findChoice($choiceList, value);
    const $choice = $(choice);
    if ($choice.is('input')) {
        $choice.prop('checked', false);
    } else if ($choice.is('option')) {
        $choice.prop('selected', false);
    }
}

export function deselectAll(choiceListSelector) {
    const $choiceList = $($(choiceListSelector));

    findAllChoices($choiceList).each((_key, element) => {
        const $choice = $(element);
        if ($choice.is('input')) {
            $choice.prop('checked', false);
        } else if ($choice.is('option')) {
            $choice.prop('selected', false);
        }
    });
}

export function getSelectedValue(choiceListSelector) {
    const values = getSelectedValues(choiceListSelector);

    return values[0] !== undefined ? values[0] : null;
}

export function getSelectedValues(choiceListSelector) {
    const $choiceList = $(choiceListSelector);

    const values = [];

    findAllChoices($choiceList).each((_key, element) => {
        const $element = $(element);
        if ($element.is('input')) {
            if ($element.is(':checked')) {
                values.push(parseInt($element.val(), 10));
            }
        } else if ($element.is('option')) {
            if ($element.is(':selected')) {
                values.push(parseInt($element.val(), 10));
            }
        }
    });

    return values;
}

export function findChoice($choiceList, value) {
    return findAllChoices($choiceList).filter((_key, element) => {
        const $element = $(element);
        return parseInt($element.val(), 10) === value;
    });
}

export function findAllChoices($choiceList) {
    return $choiceList.find('input, option');
}

export function getNewIndex($choiceList) {
    let maxIndex = 0;
    findAllChoices($choiceList).each((_key, element) => {
        const $input = $(element);
        const index = parseInt($input.attr('name').replace(/.*\[(.+)\]/, '$1'), 10);
        if (index > maxIndex) {
            maxIndex = index;
        }
    });

    return maxIndex + 1;
}
