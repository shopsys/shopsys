import { Icon } from 'components/Basic/Icon/Icon';
import { FC } from 'react';

export const SelectedParametersIcon: FC<{ onClick: () => void; dataTestId?: string }> = ({ onClick, dataTestId }) => (
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
