import { Icon } from '../Icon/Icon';

type LoaderProps = {
    iconSize?: number;
};

export const Loader: FC<LoaderProps> = ({ iconSize, className }) => {
    return <Icon icon="Spinner" iconType="icon" width={iconSize} height={iconSize} className={className} />;
};
