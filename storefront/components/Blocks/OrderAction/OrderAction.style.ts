import Button from 'components/Forms/Button';
import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

const localVariables = {
    orderActionButtonBackIconColor: '#747474',
} as const;

type OrderActionStyledProps = {
    withGapBottom?: boolean;
    withGapTop?: boolean;
};

export const OrderActionStyled = styled.div<OrderActionStyledProps>`
    ${({ theme, withGapBottom, withGapTop }) => css`
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        align-items: center;
        ${withGapBottom &&
        css`
            margin-bottom: 48px;
        `};
        ${withGapTop &&
        css`
            margin-top: 30px;
        `};

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
            ${withGapBottom &&
            css`
                margin-bottom: 90px;
            `};
        }
    `}
`;

export const OrderActionLeftStyled = styled.div`
    ${({ theme }) => css`
        order: 2;

        @media ${theme.mediaQueries.queryLg} {
            order: 1;
        }
    `}
`;

export const OrderActionRightStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 30px;
        order: 1;
        width: auto;

        @media ${theme.mediaQueries.queryLg} {
            order: 2;
            margin-bottom: 0;
        }
    `}
`;

export const OrderActionLinkBackStyled = styled.a`
    ${({ theme }) => css`
        text-transform: uppercase;
        font-weight: 700;
        color: ${theme.color.base};
        text-decoration: none;
    `}
`;

export const OrderActionButtonBackStyled = styled(Button)`
    font-weight: 700;
    text-transform: uppercase;
`;

export const OrderActionButtonBackIconStyled = styled(Icon)`
    position: relative;
    top: 2px;
    transform: rotate(90deg);
    margin-right: 5px;

    color: ${localVariables.orderActionButtonBackIconColor};
`;

export const OrderActionButtonNextIconStyled = styled(Icon)`
    ${({ theme }) => css`
        position: relative;
        top: 1px;
        transform: rotate(-90deg);
        margin-left: 5px;

        color: ${theme.color.white};
    `}
`;
