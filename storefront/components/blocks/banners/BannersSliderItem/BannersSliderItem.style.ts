import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

type StyledBannersSliderItemProps = {
    sliderItemImageUrl: string;
};

export const StyledBannersSliderItem = styled.div<StyledBannersSliderItemProps>`
    ${({ theme, sliderItemImageUrl }: { theme: Theme } & StyledBannersSliderItemProps) => css`
        height: 100%;

        background: ${`url(${sliderItemImageUrl}) center  no-repeat`};
        background-size: cover;
        border-radius: ${theme.radius.big};
    `}
`;
