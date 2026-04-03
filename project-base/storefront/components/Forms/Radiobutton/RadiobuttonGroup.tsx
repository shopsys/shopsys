import { ChangeEventHandler, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';
import { RadiobuttonOptionType } from 'types/radiobuttonOptions';
import { Radiobutton } from './Radiobutton';

type RadiobuttonGroupProps = {
    name: string;
    render: (input: ReactElement, key: string) => ReactElement<any, any> | null;
    radiobuttons: RadiobuttonOptionType[];
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
