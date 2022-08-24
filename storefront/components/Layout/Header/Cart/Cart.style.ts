import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type CartDetailStyledProps = {
    containsProducts: boolean;
    isHovered: boolean;
};

type CartBlockStyledProps = {
    isHovered: boolean;
};

const localVariables = {
    cartItemCountSize: '15px',
    cartButtonMobileSize: '40px',
} as const;

export const CartStyled = styled.div`
    ${({ theme }) => css`
        position: relative;

        @media ${theme.mediaQueries.queryLg} {
            display: flex;
        }
    `}
`;

export const CartBlockStyled = styled.a<CartBlockStyledProps>`
    ${({ theme, isHovered }) => css`
        display: none;
        align-items: center;
        padding: 15px 7px 15px 15px;

        text-decoration: none;
        transition: ${theme.transition};
        background-color: ${theme.color.orangeLight};
        color: ${theme.color.black};
        border-radius: ${theme.radius.big};

        @media ${theme.mediaQueries.queryLg} {
            display: flex;
        }

        ${isHovered &&
        css`
            border-radius: ${theme.radius.big} ${theme.radius.big} 0 0;
            box-shadow: 0 10px 20px 0 rgba(${theme.color.black}, 0.15);
            background-color: ${theme.color.white};
        `}

        &:hover {
            text-decoration: none;
            color: ${theme.color.black};
        }
    `}
`;

export const CartPiecesStyled = styled.span`
    ${() => css`
        position: relative;
        display: flex;

        font-size: 18px;
    `}
`;

export const CartValueStyled = styled.span`
    ${({ theme }) => css`
        display: none;
        margin-left: 15px;
        line-height: 18px;

        font-size: ${theme.fontSize.small};
        font-weight: 700;

        @media ${theme.mediaQueries.queryLg} {
            display: block;
        }
    `}
`;

export const CartCountStyled = styled.span`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        right: 3px;
        top: 4px;
        width: ${localVariables.cartItemCountSize};
        height: ${localVariables.cartItemCountSize};

        border-radius: 100%;
        background-color: ${theme.color.primary};
        color: ${theme.color.white};
        font-weight: 700;
        font-size: 10px;

        @media ${theme.mediaQueries.queryLg} {
            top: -7px;
            right: -8px;
        }
    `}
`;

export const CartDetailStyled = styled.div<CartDetailStyledProps>`
    ${({ theme, containsProducts, isHovered }) => css`
        display: none;

        @media ${theme.mediaQueries.queryLg} {
            display: block;
            position: absolute;
            top: 100%;
            right: 0;
            z-index: ${theme.zIndex.cart};
            padding: ${containsProducts ? '5px 20px 25px' : '15px 0 15px 15px'};
            min-width: ${containsProducts ? '510px' : '400px'};
            max-width: ${containsProducts ? '510px' : '400px'};

            transition: all 0.2s ease-in-out;
            will-change: transform;
            opacity: 0;
            transform-origin: right top;
            transform: scaleY(0.5) scaleX(0.75);
            pointer-events: none;
            box-shadow: 0 5px 10px 0 rgba(164, 167, 193, 0.34);
            border-radius: ${theme.radius.big} 0 ${theme.radius.big} ${theme.radius.big};
            background-color: ${theme.color.white};

            ${isHovered &&
            css`
                opacity: 1;
                transform: scale(1);
                pointer-events: auto;
            `}
        }
    `}
`;
export const CartDetailList = styled.ul`
    display: flex;
    flex-direction: column;
    margin: 0;
    max-height: 355px;
    overflow-y: auto;
    padding: 0;
    width: 100%;

    list-style: none;
`;

export const CartDetailFigureStyled = styled.div`
    ${() => css`
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        height: 80px;
    `}
`;

export const CartDetailTextStyled = styled.span`
    ${({ theme }) => css`
        max-width: 150px;

        font-size: ${theme.fontSize.default};
        color: ${theme.color.base};
        font-weight: 400;
    `}
`;

export const CartDetailImageStyled = styled.img`
    ${() => css`
        height: 85px;
        overflow: hidden;
    `}
`;

export const CartButtonMobileStyled = styled.div`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryTablet} {
            display: flex;
            align-items: center;
            justify-content: center;
            width: ${localVariables.cartButtonMobileSize};
            height: ${localVariables.cartButtonMobileSize};
            margin-left: 4px;

            outline: 0;
            cursor: pointer;
            font-size: 18px;
        }
    `}
`;

export const CartButtonMobileLinkStyled = styled.a`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: ${theme.transition} color;
        width: 100%;
        height: 100%;

        color: ${theme.color.white};
        text-decoration: none;

        &:hover {
            text-decoration: none;
            color: ${theme.color.white};
        }
    `}
`;

export const CartIconStyled = styled(Icon)`
    height: 18px;
    width: 18px;
`;

export const CartIconMobileStyled = styled(CartIconStyled)`
    ${({ theme }) => css`
        color: ${theme.color.white};
    `}
`;

export const CartDetailButtonWrapperStyled = styled.div`
    width: 100%;
    display: flex;
    justify-content: end;
    padding-top: 20px;
`;
