import { LabelWrapper } from '../Lib/LabelWrapper';
import { Image } from 'components/Basic/Image/Image';
import { forwardRef, InputHTMLAttributes, MouseEventHandler, ReactNode, useCallback } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { ImageSizesFragmentApi } from 'graphql/requests/images/fragments/ImageSizesFragment.generated';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id',
    'disabled' | 'name' | 'onBlur' | 'checked' | 'onChange'
>;

export type RadiobuttonProps = NativeProps & {
    value: any;
    checked: InputHTMLAttributes<HTMLInputElement>['checked'];
    dataTestId?: string;
    label: ReactNode;
    image?: ImageSizesFragmentApi | null;
    onChangeCallback?: (newValue: string | null) => void;
};

export const Radiobutton = forwardRef<HTMLInputElement, RadiobuttonProps>(
    (
        { label, image, onChangeCallback, onChange, id, name, checked, value, disabled, dataTestId, onBlur },
        radiobuttonForwardedRef,
    ) => {
        const onClickHandler: MouseEventHandler<HTMLInputElement> = useCallback(
            (event) => {
                if (!onChangeCallback) {
                    return;
                }

                if (checked) {
                    onChangeCallback(null);
                } else {
                    onChangeCallback(event.currentTarget.value);
                }
            },
            [checked, onChangeCallback],
        );

        return (
            <LabelWrapper
                htmlFor={id}
                label={
                    <div>
                        {!!image && (
                            <Image alt={image.name} type="default" image={image} className="mr-3 h-6 max-h-full w-11" />
                        )}
                        {label}
                    </div>
                }
                inputType="radio"
            >
                <input
                    className="peer sr-only"
                    value={value}
                    name={name}
                    disabled={disabled}
                    checked={checked}
                    id={id}
                    type="radio"
                    onClick={onClickHandler}
                    onBlur={onBlur}
                    onChange={onChange}
                    ref={radiobuttonForwardedRef}
                    readOnly={!onChange}
                    data-testid={dataTestId}
                />
            </LabelWrapper>
        );
    },
);

Radiobutton.displayName = 'Radiobutton';
