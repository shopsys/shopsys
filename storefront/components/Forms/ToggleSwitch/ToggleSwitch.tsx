import { ToggleSwitchLabel, ToggleSwitchStyled, ToggleSwitchWrapper } from './ToggleSwitch.style';
import { FC } from 'react';
import { ControllerRenderProps } from 'react-hook-form';

type ToggleSwitchProps = {
    id: string;
    fieldRef?: ControllerRenderProps<any, any>;
};

export const ToggleSwitch: FC<ToggleSwitchProps> = ({ id, fieldRef }) => (
    <ToggleSwitchWrapper>
        <ToggleSwitchStyled id={id} type="checkbox" checked={fieldRef?.value} {...fieldRef} />
        <ToggleSwitchLabel htmlFor={fieldRef?.name} />
    </ToggleSwitchWrapper>
);
