import { css } from 'styled-components';
import { HTMLAttributes } from 'react';
import { styled } from 'components/Theme/main';

type BannersSliderItemStyledProps = HTMLAttributes<HTMLImageElement>;

export const BannersSliderItemStyled = styled.img<BannersSliderItemStyledProps>`
    ${({ theme }) => css`
        border-radius: ${theme.radius.big};
        display: block;
        height: 100%;
        object-fit: cover;
        width: 100%;
    `}
`;
