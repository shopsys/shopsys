import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type BannersSliderItemStyledProps = {
    sliderItemImageUrl: string;
};

export const BannersSliderItemStyled = styled.div<BannersSliderItemStyledProps>`
    ${({ theme, sliderItemImageUrl }) => css`
        height: 100%;

        background: ${`url(${sliderItemImageUrl}) center  no-repeat`};
        background-size: cover;
        border-radius: ${theme.radius.big};
    `}
`;
