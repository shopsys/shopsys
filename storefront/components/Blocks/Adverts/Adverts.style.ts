import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

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
