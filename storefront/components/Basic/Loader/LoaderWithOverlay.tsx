import { Loader } from './Loader';
import { LoadingOverlayStyled } from './LoaderWithOverlay.style';
import { FC } from 'react';

type LoaderWithOverlayProps = {
    iconSize?: number;
    color?: string;
};

export const LoaderWithOverlay: FC<LoaderWithOverlayProps> = ({ iconSize, color }) => {
    return (
        <LoadingOverlayStyled>
            <Loader iconSize={iconSize} color={color} />
        </LoadingOverlayStyled>
    );
};
