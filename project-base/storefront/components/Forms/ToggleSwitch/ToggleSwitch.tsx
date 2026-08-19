import { forwardRef, InputHTMLAttributes } from 'react';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange' | 'name',
    'onBlur'
>;

type ToggleSwitchProps = NativeProps & {
    value: any;
    ariaLabel: string;
};

export const ToggleSwitch = forwardRef<HTMLInputElement, ToggleSwitchProps>(
    ({ id, name, onChange, value, onBlur, ariaLabel }, toggleSwitchForwardedRef) => (
        <div className="relative flex h-6 w-11 shrink-0 items-center justify-center">
            <input
                aria-label={ariaLabel}
                aria-checked={!!value}
                checked={!!value}
                className="peer absolute inset-0 z-above size-full cursor-pointer appearance-none rounded-full outline-hidden"
                id={id}
                name={name}
                ref={toggleSwitchForwardedRef}
                role="switch"
                type="checkbox"
                onBlur={onBlur}
                onChange={onChange}
            />
            <span
                aria-hidden="true"
                className={twJoin(
                    'pointer-events-none absolute inset-0 rounded-full bg-input-border-default transition-colors duration-200 ease-out',
                    "after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-icon-inverted after:shadow-sm after:transition-transform after:duration-200 after:ease-out after:content-['']",
                    'peer-checked:bg-input-fill peer-checked:after:translate-x-5 peer-hover:bg-input-border-hovered peer-active:after:scale-95',
                    'peer-focus-visible:outline-2 peer-focus-visible:outline-input-border-active peer-focus-visible:outline-offset-2',
                )}
            />
        </div>
    ),
);

ToggleSwitch.displayName = 'ToggleSwitch';
