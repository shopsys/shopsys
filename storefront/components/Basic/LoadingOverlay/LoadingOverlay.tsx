import { Icon } from '../Icon/Icon';
import { LoadingOverlayStyled } from './LoadingOverlay.style';
import { FC } from 'react';

type LoadingOverlayProps = {
    iconSize: number;
};

export const LoadingOverlay: FC<LoadingOverlayProps> = ({ iconSize }) => {
    return (
        <LoadingOverlayStyled>
            <Icon icon="Spinner" iconType="icon" width={iconSize} height={iconSize} />
        </LoadingOverlayStyled>
    );
};
