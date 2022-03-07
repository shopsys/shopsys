import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type AdvertsStyledProps = {
    withGap?: boolean;
};

export const AdvertsStyled = styled.div<AdvertsStyledProps>`
    ${({ withGap }) => css`
        ${withGap &&
        css`
            margin-bottom: 32px;
        `}
    `}
`;

export const AdvertsLinkStyled = styled.a`
    display: flex;
`;
