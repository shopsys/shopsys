import { styled } from 'components/Theme/main';
import NextImage from 'next/image';
import { css } from 'styled-components';

export const LogoStyled = styled(NextImage)(
    ({ theme }) => css`
        display: flex;
        width: 100%;
        max-width: 120px;

        @media ${theme.mediaQueries.queryLg} {
            max-width: 163px;
        }
    `,
);
