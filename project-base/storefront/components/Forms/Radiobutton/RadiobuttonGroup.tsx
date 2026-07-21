import { ChangeEventHandler, ReactElement } from 'react';
import { Control, FieldPath, FieldValues, useController } from 'react-hook-form';
import { FunctionComponentProps } from 'types/globals';
import { RadiobuttonOptionType } from 'types/radiobuttonOptions';
import { Radiobutton } from './Radiobutton';

type RadiobuttonGroupProps<TFieldValues extends FieldValues, TTransformedValues> = {
    name: FieldPath<TFieldValues>;
    render: (input: ReactElement, key: string) => ReactElement<any, any> | null;
    radiobuttons: RadiobuttonOptionType[];
    control: Control<TFieldValues, any, TTransformedValues>;
    formName: string;
    onChange?: ChangeEventHandler<HTMLInputElement>;
};

export const RadiobuttonGroup = <TFieldValues extends FieldValues, TTransformedValues = TFieldValues>({
    name,
    radiobuttons,
    control,
    render,
    formName,
    onChange,
}: RadiobuttonGroupProps<TFieldValues, TTransformedValues> & FunctionComponentProps) => {
    const { field } = useController({ name, control });

    const onChangeHandler: ChangeEventHandler<HTMLInputElement> = (event) => {
        field.onChange(event);

        if (onChange) {
            onChange(event);
        }
    };

    return (
        <>
            {radiobuttons.map((radiobuttonOption, index) => {
                const radiobuttonId = formName + name + (radiobuttonOption.id ?? index);

                return render(
                    <Radiobutton
                        {...field}
                        onChange={onChangeHandler}
                        {...radiobuttonOption}
                        checked={field.value === radiobuttonOption.value}
                        id={radiobuttonId}
                    />,
                    radiobuttonId,
                );
            })}
        </>
    );
};
