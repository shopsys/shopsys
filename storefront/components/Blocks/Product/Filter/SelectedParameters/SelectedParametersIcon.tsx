import { Icon } from 'components/Basic/Icon/Icon';

export const SelectedParametersIcon: FC<{ onClick: () => void }> = ({ onClick, dataTestId }) => (
    <Icon
        iconType="icon"
        icon="RemoveThin"
        width={13}
        height={13}
        onClick={onClick}
        className="ml-3 cursor-pointer"
        data-testid={dataTestId}
    />
);
