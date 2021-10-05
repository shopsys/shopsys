import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const HeadingWrapperStyled = styled.div`
    text-align: center;
`;

export const SimpleLayoutStyled = styled.div`
    width: 100%;
    display: flex;
    justify-content: center;
    margin-bottom: 100px;
`;

export const SimpleLayoutContentStyled = styled.div`
    ${({ theme }) => css`
        margin-top: 28px;
        margin-bottom: 0;
        width: 100%;
        padding: 20px 30px 15px;

        border: 3px solid ${theme.color.greyLighter};
        border-radius: 22px;

        @media ${theme.mediaQueries.queryLg} {
            width: 690px;
            padding: 40px 60px 30px;
        }
    `}
`;
