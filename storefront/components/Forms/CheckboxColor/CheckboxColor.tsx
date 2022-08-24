import { CheckboxColorStyled } from './CheckboxColor.style';
import { ColorLabelWrapper } from 'components/Forms/Lib/ColorLabelWrapper/ColorLabelWrapper';
import { FC, InputHTMLAttributes } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import tinycolor from 'tinycolor2';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name',
    'id' | 'required' | 'disabled'
>;

type CheckboxColorProps = NativeProps & {
    label?: string;
    bgColor?: string;
} & (
        | {
              value: unknown;
              fieldRef?: never;
              onChange: (...event: any[]) => void;
          }
        | {
              value?: never;
              fieldRef: ControllerRenderProps<any, any>;
              onChange?: never;
          }
    );

export const CheckboxColor: FC<CheckboxColorProps> = ({
    bgColor = '#d4d4d4',
    label,
    fieldRef,
    id,
    name,
    disabled,
    required,
    value,
    onChange,
}) => {
    return (
        <ColorLabelWrapper
            label={label}
            htmlFor={id === undefined ? name + 'checkbox_color-id' : id}
            bgColor={bgColor}
            isLightColor={tinycolor(bgColor).isLight()}
            isDisabled={disabled}
            isActive={fieldRef ? fieldRef.value : value}
        >
            <CheckboxColorStyled
                disabled={disabled}
                required={required}
                id={id === undefined ? name + 'checkbox_color-id' : id}
                {...(fieldRef ?? {})}
                checked={fieldRef ? fieldRef.value : value}
                onChange={fieldRef ? fieldRef.onChange : onChange}
                type="checkbox"
            />
        </ColorLabelWrapper>
    );
};
