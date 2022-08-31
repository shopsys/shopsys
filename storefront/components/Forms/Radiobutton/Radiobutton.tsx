import { LabelWrapper } from '../Lib/LabelWrapper/LabelWrapper';
import { LabelImageWrapper, RadiobuttonStyled } from './Radiobutton.style';
import { Image } from 'components/Basic/Image/Image';
import { forwardRef, InputHTMLAttributes, MouseEventHandler, ReactNode, useCallback } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { ImageType } from 'types/image';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id',
    'disabled' | 'name' | 'onBlur' | 'checked' | 'onChange'
>;

export type RadiobuttonProps = NativeProps & {
    value: any;
    checked: InputHTMLAttributes<HTMLInputElement>['checked'];
    testIdentifier?: string;
    label: ReactNode;
    image?: ImageType | null;
    onChangeCallback?: (newValue: string | null) => void;
};

export const Radiobutton = forwardRef<HTMLInputElement, RadiobuttonProps>(
    (
        { label, image, onChangeCallback, onChange, id, name, checked, value, disabled, testIdentifier, onBlur },
        radiobuttonForwardedRef,
    ) => {
        const onClickHandler: MouseEventHandler<HTMLInputElement> = useCallback(
            (event) => {
                if (onChangeCallback === undefined) {
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
                        {image !== undefined && (
                            <LabelImageWrapper>
                                <Image alt="" type="default" image={image} />
                            </LabelImageWrapper>
                        )}
                        {label}
                    </div>
                }
                inputType="radio"
            >
                <RadiobuttonStyled
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
                    readOnly={onChange === undefined}
                    data-testid={testIdentifier}
                />
            </LabelWrapper>
        );
    },
);

Radiobutton.displayName = 'Radiobutton';
