import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const FilterStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        overflow: hidden;
        padding: 0 14px;
        z-index: ${theme.zIndex.aboveOverlay}; // on mobile is menu above ovarlay

        background-color: ${theme.color.blueLight};
        border-radius: ${theme.radius.big};

        @media ${theme.mediaQueries.queryVl} {
            z-index: ${theme.zIndex.above};
        }
    `}
`;
