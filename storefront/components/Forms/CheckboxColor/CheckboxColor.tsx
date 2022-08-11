import { CheckboxColorStyled } from './CheckboxColor.style';
import ColorLabelWrapper from 'components/Forms/Lib/ColorLabelWrapper';
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
    /**
     * Display Label of the HTML checkbox element
     */
    label?: string;
    /**
     * Background color of color chooser
     */
    bgColor?: string;
    /**
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
} & (
        | {
              /**
               * props that are by default included in the fieldRef, but can be used, if a complete fieldRef cannot be provided
               */
              value: unknown;
              /**
               * a ref of the controlled field element used for hooking onto the field events/changes
               */
              fieldRef?: never;
              /**
               * props that are by default included in the fieldRef, but can be used, if a complete fieldRef cannot be provided
               */
              onChange: (...event: any[]) => void;
          }
        | {
              /**
               * props that are by default included in the fieldRef, but can be used, if a complete fieldRef cannot be provided
               */
              value?: never;
              /**
               * a ref of the controlled field element used for hooking onto the field events/changes
               */
              fieldRef: ControllerRenderProps<any, any>;
              /**
               * props that are by default included in the fieldRef, but can be used, if a complete fieldRef cannot be provided
               */
              onChange?: never;
          }
    );
/**
 * CheckboxColor - circle color with invisible checkbox, selected color will display tick
 */
const CheckboxColor: FC<CheckboxColorProps> = ({
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

/* @component */
export default CheckboxColor;
