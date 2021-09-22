import Button from 'components/Forms/Button';
import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';
import TextInput from 'components/Forms/TextInput';

type PromoCodeProps = {
    contentElementHeight: number;
};

const localVariables = {
    promoCodeButtonBackgroundHover: '#ffeaaa',
} as const;

export const PromoCodeStyled = styled.div<PromoCodeProps>`
    ${({ theme, contentElementHeight }) => css`
        @media ${theme.mediaQueries.queryVl} {
            width: 300px;
        }
        .promoCode-enter {
            height: 0;
        }

        .promoCode-enter-active {
            height: ${contentElementHeight}px;
            transition: 0.3s all ease;
        }

        .promoCode-exit {
            height: ${contentElementHeight}px;
        }

        .promoCode-exit-active {
            height: 0;
            transition: 0.3s all ease;
        }
    `}
`;

export const PromoCodeButtonStyled = styled.div`
    ${({ theme }) => css`
        display: inline-flex;
        align-items: center;
        padding: 11px 16px;
        margin-bottom: 10px;
        width: 100%;

        background-color: ${theme.color.orangeLight};
        border-radius: ${theme.radius.medium};
        color: ${theme.color.grey};
        text-decoration: none;
        text-transform: uppercase;
        transition: ${theme.transition};
        font-weight: 700;
        font-size: ${theme.fontSize.small};
        cursor: pointer;

        @media ${theme.mediaQueries.queryLg} {
            max-width: 250px;
        }

        &:hover {
            background-color: ${localVariables.promoCodeButtonBackgroundHover};
        }
    `}
`;

export const PromoCodeButtonIconStyled = styled(Icon)`
    width: 12px;
    height: 12px;
    margin-right: 10px;
`;

export const PromoCodeContentWrapperStyled = styled.div`
    overflow: hidden;
`;

export const PromoCodeContentStyled = styled.div`
    display: flex;
`;

export const PromoCodeContentButtonStyled = styled(Button)`
    ${({ theme }) => css`
        padding-left: 10px;
        padding-right: 10px;

        border-radius: 0 ${theme.radius.big} ${theme.radius.big} 0;
    `}
`;

export const PromoCodeContentInputStyled = styled(TextInput)`
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: 0;
`;
