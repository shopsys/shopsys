import { css } from 'styled-components';
import { styled } from 'theme/main';

export const CartProductNameStyled = styled.div`
    ${({ theme }) => css`
        text-align: left;
        padding-right: 15px;
        height: 100%;

        @media ${theme.mediaQueries.queryVl} {
            width: 270px;
        }
    `}
`;

export const CartProductNameTitleStyled = styled.a`
    ${({ theme }) => css`
        font-size: ${theme.fontSize.small};
        font-weight: 700;
        line-height: 18px;
        text-transform: uppercase;
        text-decoration: none;
        color: ${theme.color.base};

        &:hover {
            text-decoration: none;
            color: ${theme.color.base};
        }
    `}
`;

export const CartProductNameTitleTextStyled = styled.span`
    margin-right: 5px;
`;

export const CartProductFlagsStyled = styled.div`
    display: inline;
    line-height: 10px;
    position: relative;
    top: -1px;
`;

export const CartProductCodeStyled = styled.div`
    ${({ theme }) => css`
        color: ${theme.color.greyLight};
        font-size: 13px;
        line-height: 19px;
    `}
`;

export const CartProductAvailabilityStyled = styled.div`
    display: block;
    flex: 1;
`;

export const CartProductAvailabilityMessageStyled = styled.span`
    ${({ theme }) => css`
        font-weight: 400;
        display: block;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            display: inline;
            margin-left: 5px;
        }
    `}
`;
