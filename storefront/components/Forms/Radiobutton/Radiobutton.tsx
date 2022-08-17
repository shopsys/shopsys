import { LabelImageWrapper, RadiobuttonStyled } from './Radiobutton.style';
import Image from 'components/Basic/Image';
import LabelWrapper from 'components/Forms/Lib/LabelWrapper';
import { FC, InputHTMLAttributes, MouseEventHandler, ReactNode } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { ImageType } from 'types/image';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name' | 'value',
    'disabled' | 'id'
>;

type RadiobuttonProps = NativeProps & {
    label: string | ReactNode | ReactNode[];
    image?: ImageType | null;
    checked?: InputHTMLAttributes<HTMLInputElement>['checked'];
} & (
        | {
              onChangeCallback: (newValue: string | null) => void;
              fieldRef?: never;
          }
        | {
              onChangeCallback?: never;
              fieldRef: ControllerRenderProps<any, any>;
          }
    );

/**
 * An HTML Radiobutton element of type radiobutton
 */
const Radiobutton: FC<RadiobuttonProps> = ({
    label,
    image,
    onChangeCallback,
    id,
    name,
    checked,
    fieldRef,
    value,
    disabled,
}) => {
    const onClickHandler: MouseEventHandler<HTMLInputElement> = (event) => {
        if (onChangeCallback === undefined) {
            return;
        }

        if (checked) {
            onChangeCallback(null);
        } else {
            onChangeCallback(event.currentTarget.value);
        }
    };

    return (
        <LabelWrapper
            htmlFor={id === undefined ? name + 'radiobutton-id' : id}
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
            {fieldRef ? (
                <RadiobuttonStyled
                    {...fieldRef}
                    value={value}
                    disabled={disabled}
                    id={id === undefined ? name + 'radiobutton-id' : id}
                    type="radio"
                />
            ) : (
                <RadiobuttonStyled
                    value={value}
                    name={name}
                    disabled={disabled}
                    checked={checked}
                    id={id === undefined ? name + 'radiobutton-id' : id}
                    type="radio"
                    onClick={onClickHandler}
                    readOnly={onChangeCallback !== undefined}
                />
            )}
        </LabelWrapper>
    );
};

export default Radiobutton;
