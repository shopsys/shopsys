import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type AdvertsStyledProps = {
    withGapBottom?: boolean;
    withGapTop?: boolean;
};

export const AdvertsStyled = styled.div<AdvertsStyledProps>`
    ${({ withGapBottom, withGapTop }) => css`
        ${withGapBottom &&
        css`
            margin-bottom: 32px;
        `}

        ${withGapTop &&
        css`
            margin-top: 32px;
        `}
    `}
`;
