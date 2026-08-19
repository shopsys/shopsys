import { ToggleSwitch } from 'components/Forms/ToggleSwitch/ToggleSwitch';
import { ReactElement } from 'react';
import { Control, FieldPath, FieldValues, useController } from 'react-hook-form';
import { FunctionComponentProps } from 'types/globals';

type ToggleSwitchControlledProps<TFieldValues extends FieldValues, TTransformedValues> = {
    ariaLabel: string;
    name: FieldPath<TFieldValues>;
    render: (input: ReactElement, inputId: string) => ReactElement<any, any> | null;
    control: Control<TFieldValues, any, TTransformedValues>;
    formName: string;
};

export const ToggleSwitchControlled = <TFieldValues extends FieldValues, TTransformedValues = TFieldValues>({
    ariaLabel,
    name,
    render,
    control,
    formName,
}: ToggleSwitchControlledProps<TFieldValues, TTransformedValues> & FunctionComponentProps) => {
    const { field } = useController({ name, control });
    const toggleSwitchId = `${formName}-${name}`;

    return render(<ToggleSwitch {...field} ariaLabel={ariaLabel} id={toggleSwitchId} />, toggleSwitchId);
};
