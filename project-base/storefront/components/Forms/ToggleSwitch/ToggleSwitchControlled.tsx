import { ToggleSwitch } from 'components/Forms/ToggleSwitch/ToggleSwitch';
import { JSX, ReactElement } from 'react';
import { Control, useController } from 'react-hook-form';

type ToggleSwitchControlledProps = {
    name: string;
    render: (input: JSX.Element) => ReactElement<any, any> | null;
    control: Control<any>;
    formName: string;
};

export const ToggleSwitchControlled: FC<ToggleSwitchControlledProps> = ({ name, render, control, formName }) => {
    const { field } = useController({ name, control });
    const toggleSwitchId = formName + '-' + name;

    return render(<ToggleSwitch {...field} id={toggleSwitchId} />);
};
