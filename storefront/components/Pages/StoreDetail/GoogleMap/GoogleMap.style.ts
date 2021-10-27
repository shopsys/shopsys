import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { StoreDetailMapSize } from 'components/Pages/StoreDetail/StoreDetail.style';
import { styled } from 'components/Theme/main';

type GoogleMapMarkerStyledProps = {
    lat: number;
    lng: number;
    icon: string;
};

export const GoogleMapWrapStyled = styled.div`
    ${({ theme }) => css`
        width: 100%;
        height: 245px;
        margin-bottom: 16px;

        @media ${theme.mediaQueries.queryLg} {
            position: absolute;
            left: 0;
            top: 0;
            width: ${StoreDetailMapSize.small};
            height: ${StoreDetailMapSize.small};
            margin-bottom: 0;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: ${StoreDetailMapSize.bigger};
            height: ${StoreDetailMapSize.bigger};
        }

        @media ${theme.mediaQueries.queryXl} {
            width: ${StoreDetailMapSize.big};
            height: ${StoreDetailMapSize.big};
        }
    `}
`;

export const GoogleMapMarkerStyled = styled(Icon)<GoogleMapMarkerStyledProps>`
    position: absolute;
    width: 50px;
    height: 60px;
    transform: translate3d(-50%, -100%, 0);

    color: #202330;
`;
