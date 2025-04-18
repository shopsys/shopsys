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
        <div className="relative h-6">
            <input
                aria-checked={value}
                checked={value}
                id={id}
                name={name}
                ref={toggleSwitchForwardedRef}
                type="checkbox"
                className={twJoin(
                    'z-above peer h-6 w-11 rounded-full outline-none',
                    'focus-visible:ring-inputBorderActive focus-visible:ring-2 focus-visible:ring-offset-2',
                )}
                onBlur={onBlur}
                onChange={onChange}
            />
            <label
                htmlFor={id}
                className={twJoin(
                    'absolute top-0 left-0 h-6 w-11 cursor-pointer rounded-full',
                    "after:my-0.5 after:ml-0.5 after:block after:size-5 after:rounded-full after:transition-all after:content-['']",
                    'peer-checked:after:ml-5.5 peer-checked:after:block',
                    'bg-icon-less after:bg-icon-inverted',
                    'peer-checked:bg-icon-accent',
                )}
            >
                <span className="sr-only">{name}</span>
            </label>
        </div>
    ),
);

ToggleSwitch.displayName = 'ToggleSwitch';
