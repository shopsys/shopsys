import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type FooterBottomStyledProps = {
    checkout?: boolean;
    orderStep?: boolean;
    cart?: boolean;
};

export const FooterStyled = styled.div`
    position: relative;
    margin-top: auto;
`;

export const FooterBottomStyled = styled.div<FooterBottomStyledProps>`
    ${({ theme, orderStep, checkout, cart }) => css`
        display: flex;
        flex-direction: column;
        padding: 20px 0 42px;

        @media ${theme.mediaQueries.queryLg} {
            padding: 45px 0;
        }

        ${orderStep &&
        css`
            padding: 45px 0;
        `};

        ${checkout &&
        css`
            padding-top: 24px;
        `};

        ${cart &&
        css`
            padding-top: 24px;
        `};
    `}
`;

export const FooterBlockStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 50px;

        @media ${theme.mediaQueries.queryVl} {
            display: flex;
            margin-bottom: 95px;
        }
    `}
`;
