import { Radiobutton, RadiobuttonProps } from './Radiobutton';
import { ChangeEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type RadiobuttonGroupProps = {
    name: string;
    render: (input: JSX.Element, key: string) => ReactElement<any, any> | null;
    radiobuttons: (Pick<RadiobuttonProps, 'disabled' | 'label' | 'value'> & Partial<Pick<RadiobuttonProps, 'id'>>)[];
    control: Control<any>;
    formName: string;
    onChange?: ChangeEventHandler<HTMLInputElement>;
};

export const RadiobuttonGroup: FC<RadiobuttonGroupProps> = ({
    name,
    radiobuttons,
    control,
    render,
    formName,
    onChange,
}) => {
    const { field } = useController({ name, control });

    const onChangeHandler: ChangeEventHandler<HTMLInputElement> = (event) => {
        field.onChange(event);

        if (onChange) {
            onChange(event);
        }
    };

    return (
        <>
            {radiobuttons.map((radiobutton, index) => {
                const radiobuttonId = formName + name + (radiobutton.id ?? index);

                return render(
                    <Radiobutton
                        {...field}
                        onChange={onChangeHandler}
                        {...radiobutton}
                        checked={field.value === radiobutton.value}
                        id={radiobuttonId}
                    />,
                    radiobuttonId,
                );
            })}
        </>
    );
};
