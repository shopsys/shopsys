import { Radiobutton, RadiobuttonProps } from './Radiobutton';
import { ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type RadiobuttonGroupProps = {
    name: string;
    render: (input: JSX.Element, key: string) => ReactElement<any, any> | null;
    radiobuttons: Pick<RadiobuttonProps, 'disabled' | 'image' | 'label' | 'value' | 'dataTestId'>[];
    control: Control<any>;
    formName: string;
};

export const RadiobuttonGroup: FC<RadiobuttonGroupProps> = ({ name, radiobuttons, control, render, formName }) => {
    const { field } = useController({ name, control });

    return (
        <>
            {radiobuttons.map((radiobutton) => {
                const radiobuttonId = formName + name + radiobutton.value;

                return render(
                    <Radiobutton
                        {...field}
                        {...radiobutton}
                        id={radiobuttonId}
                        checked={field.value === radiobutton.value}
                    />,
                    radiobuttonId,
                );
            })}
        </>
    );
};
