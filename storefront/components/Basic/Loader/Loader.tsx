import { Icon } from '../Icon/Icon';
import { FC } from 'react';

type LoaderProps = {
    iconSize?: number;
    color?: string;
};

export const Loader: FC<LoaderProps> = ({ iconSize, color }) => {
    return <Icon icon="Spinner" iconType="icon" width={iconSize} height={iconSize} color={color} />;
};
