import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

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
        margin-top: 0;
        margin-bottom: 0;
        width: 100%;
        padding: 20px 10px 15px;

        border: 3px solid ${theme.color.greyLighter};
        border-radius: 22px;

        @media ${theme.mediaQueries.queryLg} {
            width: 690px;
            padding: 40px 60px 30px;
            margin-top: 28px;
        }
    `}
`;
