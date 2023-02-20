import { Loader } from './Loader';
import { LoadingOverlayStyled } from './LoaderWithOverlay.style';
import { FC } from 'react';

type LoaderWithOverlayProps = {
    iconSize?: number;
};

export const LoaderWithOverlay: FC<LoaderWithOverlayProps> = ({ iconSize }) => {
    return (
        <LoadingOverlayStyled>
            <Loader iconSize={iconSize} />
        </LoadingOverlayStyled>
    );
};
