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
    label: string | ReactNode | ReactNode[];
    count?: number;
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
                disabled={disabled}
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

export default Checkbox;
