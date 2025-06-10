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
};

export const ToggleSwitch = forwardRef<HTMLInputElement, ToggleSwitchProps>(
    ({ id, name, onChange, value, onBlur }, toggleSwitchForwardedRef) => (
        <div className="relative flex h-6 w-11 items-center justify-center">
            <input
                aria-checked={value}
                checked={value}
                id={id}
                name={name}
                ref={toggleSwitchForwardedRef}
                role="switch"
                type="checkbox"
                className={twJoin(
                    'peer h-5 w-10 outline-none',
                    'focus-visible:ring-input-border-active focus-visible:ring-2 focus-visible:ring-offset-4',
                )}
                onBlur={onBlur}
                onChange={onChange}
            />
            <label
                htmlFor={id}
                className={twJoin(
                    'absolute size-full cursor-pointer rounded-full',
                    'bg-input-border-default peer-not-checked:hover:bg-input-border-hovered',
                    "after:bg-icon-inverted hover:after:bg-fill-accent-less after:my-0.5 after:ml-0.5 after:block after:size-5 after:rounded-full after:transition-all after:content-['']",
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
