import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { twMergeCustom } from 'utils/twMerge';

type ChoiceIndicatorProps = {
    inputType: 'checkbox' | 'radio';
    checked?: boolean;
    disabled?: boolean;
    className?: string;
};

/**
 * The visual box of a checkbox or the circle of a radio button; the input itself is visually hidden.
 */
export const ChoiceIndicator: FC<ChoiceIndicatorProps> = ({ inputType, checked, disabled, className }) => (
    <span
        className={twMergeCustom(
            'flex size-5 min-w-5 border border-input-border-default bg-input-bg-default p-0.75 transition',
            inputType === 'checkbox' ? 'rounded-checkbox' : 'rounded-full p-1.25',
            checked ? 'border-input-border-active bg-input-fill' : 'border-2',
            !disabled && [
                // the halo keeps the hover readable on a checked indicator, where the fill only darkens a little
                'choice-hover:ring-4 choice-hover:ring-input-glow-hovered active:scale-90',
                checked
                    ? 'choice-hover:border-input-fill-hovered choice-hover:bg-input-fill-hovered'
                    : 'choice-hover:border-input-fill choice-hover:bg-fill-accent-less group-active:border-input-fill',
            ],
            disabled && 'border-input-border-disabled',
            disabled && checked && 'bg-input-border-disabled',
            className,
        )}
    >
        {inputType === 'checkbox' ? (
            <CheckmarkIcon
                aria-hidden="true"
                className={twMergeCustom(
                    'h-full text-icon-inverted opacity-0 transition',
                    checked && 'opacity-100',
                    disabled && 'text-input-text-disabled',
                )}
            />
        ) : (
            <span
                className={twMergeCustom(
                    'h-full w-full rounded-full bg-icon-inverted opacity-0 transition',
                    checked && 'opacity-100',
                )}
            />
        )}
    </span>
);
