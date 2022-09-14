import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type GoogleMapMarkerStyledProps = {
    lat: number | null;
    lng: number | null;
    isActive?: boolean;
    isDetail?: boolean;
};

const localVariables = {
    GoogleMapMarkerColor: '#202330',
    GoogleMapMarkerActiveColor: '#ffd637',
};

export const GoogleMapWrapStyled = styled.div`
    width: 100%;
    height: 100%;
`;

export const GoogleMapMarkerStyled = styled(Icon)<GoogleMapMarkerStyledProps>(
    ({ isActive, isDetail }) => css`
        position: absolute;
        width: ${isActive === true ? `40px` : `32px`};
        height: ${isActive === true ? `48px` : `38px`};
        transform: translate3d(-50%, -100%, 0);
        cursor: pointer;

        color: ${isActive === true
            ? `${localVariables.GoogleMapMarkerActiveColor}`
            : `${localVariables.GoogleMapMarkerColor}`};

        ${isDetail === true &&
        css`
            width: 50px;
            height: 60px;
            cursor: default;
        `}
    `,
);
