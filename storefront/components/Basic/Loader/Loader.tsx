import { Icon } from '../Icon/Icon';
import { FC } from 'react';

type LoaderProps = {
    iconSize?: number;
    className?: string;
};

export const Loader: FC<LoaderProps> = ({ iconSize, className }) => {
    return <Icon icon="Spinner" iconType="icon" width={iconSize} height={iconSize} className={className} />;
};
