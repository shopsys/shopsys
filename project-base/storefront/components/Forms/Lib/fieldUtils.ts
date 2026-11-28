import { getButtonHeightClassName } from 'components/Forms/Button/buttonUtils';
import { twMergeCustom } from 'utils/twMerge';

export type FieldSize = 'small' | 'default';

/**
 * The default field is 56px high on every width, so the floating label and the value have room (8px above and below).
 * The small field has no room for a roomy label and is as high as a medium button.
 */
export const getFieldHeightClassName = (fieldSize: FieldSize = 'default') =>
    fieldSize === 'small' ? getButtonHeightClassName('medium') : 'h-14';

/**
 * The floating label above the value and the top padding that keeps the value under it, for a default-size field.
 */
export const FIELD_FLOATING_LABEL_CLASS_NAME = 'top-2 text-xs';
export const FIELD_VALUE_PADDING_CLASS_NAME = 'pt-4';

/**
 * The 1px border, background and text of every field; hover darkens the border.
 */
const FIELD_BASE_CLASS_NAME =
    'border border-input-border-default bg-input-bg-default text-input-text-default transition hover:border-input-border-hovered';

type FieldStateOptions = {
    hasError?: boolean;
    hasWarning?: boolean;
};

/**
 * Border and focus of a field. Select and multiselect use only the border part, their focus is handled by
 * FIELD_KEYBOARD_FOCUS_WITHIN_CLASS_NAME and FIELD_OPEN_CLASS_NAME.
 * A click, an error and a warning thicken the border to 2px with an inset ring, so the content does not move.
 * Keyboard focus fills the whole field like buttons and links do.
 */
export const getFieldStateClassName = ({ hasError, hasWarning }: FieldStateOptions = {}) => {
    const hasValidationState = hasError || hasWarning;
    const hasOnlyWarning = hasWarning && !hasError;

    // Merged, so the hover and border colours of an error or a warning replace the default ones.
    return twMergeCustom(
        FIELD_BASE_CLASS_NAME,
        !hasValidationState &&
            'focus:inset-ring-1 focus:inset-ring-input-border-hovered focus:border-input-border-hovered',
        hasError &&
            'inset-ring-1 inset-ring-input-border-error border-input-border-error hover:border-input-border-error',
        hasOnlyWarning &&
            'inset-ring-1 inset-ring-input-border-warning border-input-border-warning hover:border-input-border-warning',
        'focus-keyboard:inset-ring-1 focus-keyboard:inset-ring-focus-inner-ring focus-keyboard:border-focus-inner-ring focus-keyboard:bg-focus-background',
        'disabled:inset-ring-0 disabled:border-input-border-disabled disabled:bg-input-bg-disabled disabled:text-input-text-disabled',
    );
};

/**
 * Open select or multiselect: the same 2px border as a text field focused by a click.
 */
export const FIELD_OPEN_CLASS_NAME = 'border-input-border-hovered inset-ring-1 inset-ring-input-border-hovered';

/**
 * Select and multiselect are focused through a button or, with a combobox, a search input inside them,
 * so keyboard focus on either fills the whole field like a text field.
 */
export const FIELD_KEYBOARD_FOCUS_WITHIN_CLASS_NAME =
    'focus-within-keyboard:border-focus-inner-ring focus-within-keyboard:bg-focus-background focus-within-keyboard:inset-ring-1 focus-within-keyboard:inset-ring-focus-inner-ring';
