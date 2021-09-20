import Button from 'components/Forms/Button';
import { css } from 'styled-components';
import Icon from '../../Basic/Icon';
import { styled } from '../../Theme/main';

const localVariables = {
    orderActionButtonBackIconColor: '#747474',
} as const;

export const OrderActionStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 48px;

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 90px;
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
