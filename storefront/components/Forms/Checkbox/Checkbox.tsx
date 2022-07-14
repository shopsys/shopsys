import { CheckboxStyled } from './Checkbox.style';
import LabelWrapper from 'components/Forms/Lib/LabelWrapper';
import { FC, InputHTMLAttributes, ReactNode } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name',
    'id' | 'disabled' | 'required'
>;

type CheckboxProps = NativeProps & {
    /**
     * A prop to decide if the input has errors
     */
    hasError?: boolean;
    /**
     * A prop to decide if the input is touched
     */
    isTouched?: boolean;
    /**
     * Display Label of the HTML checkbox element
     */
    label: string | ReactNode | ReactNode[];
    /**
     * Display count of items. This is an optional prop primary from the parameters filter.
     */
    count?: number;
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
 * An HTML Checkbox element of type checkbox
 */
const Checkbox: FC<CheckboxProps> = ({ id, name, label, count, required, disabled, fieldRef, onChange, value }) => {
    return (
        <LabelWrapper
            label={label}
            count={count}
            required={required}
            htmlFor={id === undefined ? name + 'checkbox-id' : id}
            inputType="checkbox"
            checked={fieldRef ? fieldRef.value : value}
        >
            <CheckboxStyled
                {...restProps}
                required={required}
                id={id === undefined ? name + 'checkbox-id' : id}
                {...(fieldRef ?? {})}
                checked={fieldRef ? fieldRef.value : value}
                onChange={fieldRef ? fieldRef.onChange : onChange}
                type="checkbox"
            />
        </LabelWrapper>
    );
};

/* @component */
export default Checkbox;
