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
        <div className="relative flex h-6 w-11 items-center justify-center">
            <input
                aria-label={ariaLabel}
                aria-checked={value}
                checked={value}
                className="peer h-5 w-10 outline-hidden"
                id={id}
                name={name}
                ref={toggleSwitchForwardedRef}
                role="switch"
                type="checkbox"
                onBlur={onBlur}
                onChange={onChange}
            />
            <label
                htmlFor={id}
                className={twJoin(
                    'absolute size-full cursor-pointer rounded-full',
                    'bg-input-border-default peer-not-checked:hover:bg-input-border-hovered',
                    "after:my-0.5 after:ml-0.5 after:block after:size-5 after:rounded-full after:bg-icon-inverted after:transition-all after:content-[''] hover:after:bg-fill-accent-less",
                    'peer-checked:after:ml-5.5 peer-checked:after:block',
                    'peer-checked:bg-input-fill',
                )}
            >
                <span className="sr-only">{name}</span>
            </label>
        </div>
    ),
);

ToggleSwitch.displayName = 'ToggleSwitch';
