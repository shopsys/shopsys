import { css } from 'styled-components';
import { styled } from 'theme/main';

type StyledBannersSliderItemProps = {
    sliderItemImageUrl: string;
};

export const StyledBannersSliderItem = styled.div<StyledBannersSliderItemProps>`
    ${({ theme, sliderItemImageUrl }) => css`
        height: 100%;

        background: ${`url(${sliderItemImageUrl}) center  no-repeat`};
        background-size: cover;
        border-radius: ${theme.radius.big};
    `}
`;
