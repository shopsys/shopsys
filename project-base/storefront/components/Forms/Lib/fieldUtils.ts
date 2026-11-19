import { getButtonHeightClassName } from 'components/Forms/Button/buttonUtils';
import { twMergeCustom } from 'utils/twMerge';

export type FieldSize = 'small' | 'default';

/**
 * A field is as high as a button of the matching size, so fields and buttons placed in one row line up.
 */
export const getFieldHeightClassName = (fieldSize: FieldSize = 'default') =>
    getButtonHeightClassName(fieldSize === 'small' ? 'medium' : 'large');

/**
 * The floating label above the value and the top padding that keeps the value under it, for a default-size field.
 */
export const FIELD_FLOATING_LABEL_CLASS_NAME = 'top-1 vl:top-1.5 text-xs';
export const FIELD_VALUE_PADDING_CLASS_NAME = 'pt-4 vl:pt-5';

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
 * Without a combobox, select and multiselect are focused through a button inside them,
 * so keyboard focus on that button fills the whole field like a text field.
 */
export const FIELD_KEYBOARD_FOCUS_WITHIN_CLASS_NAME =
    'has-[button:focus-visible]:border-focus-inner-ring has-[button:focus-visible]:bg-focus-background has-[button:focus-visible]:inset-ring-1 has-[button:focus-visible]:inset-ring-focus-inner-ring';
