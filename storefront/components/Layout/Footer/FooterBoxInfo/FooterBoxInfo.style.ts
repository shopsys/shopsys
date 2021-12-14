import Button from 'components/Forms/Button';
import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

const localVariables = {
    footerBoxInfoImageSize: '61px',
    footerBoxInfoImageSizeSmall: '47px',
};

type FooterBoxInfoStyledProps = {
    orderStep?: boolean;
};

export const FooterBoxInfoStyled = styled.div<FooterBoxInfoStyledProps>`
    ${({ theme, orderStep }) => css`
        position: relative;
        display: flex;
        align-items: center;
        margin-bottom: 45px;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 90px;
        }

        ${orderStep &&
        css`
            margin: 0;
        `};
    `}
`;

export const FooterBoxInfoImageStyled = styled.img`
    ${({ theme }) => css`
        position: absolute;
        left: 0;
        bottom: 0;
        transform: translateY(50%);
        display: block;
        min-width: ${localVariables.footerBoxInfoImageSizeSmall};
        width: ${localVariables.footerBoxInfoImageSizeSmall};
        height: ${localVariables.footerBoxInfoImageSizeSmall};

        @media ${theme.mediaQueries.queryLg} {
            min-width: ${localVariables.footerBoxInfoImageSize};
            width: ${localVariables.footerBoxInfoImageSize};
            height: ${localVariables.footerBoxInfoImageSize};
        }
    `}
`;

export const FooterBoxInfoContentStyled = styled.div<FooterBoxInfoStyledProps>`
    ${({ theme, orderStep }) => css`
        position: relative;
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        flex: 1;
        margin-left: 70px;
        padding: 14px;

        background-color: ${theme.color.primary};
        border-radius: ${theme.radius.big};

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            margin-left: 90px;
            padding: 21px 21px 21px 30px;
        }

        ${orderStep &&
        css`
            padding: 0;
            margin: 0;
        `};

        &::before {
            content: '';
            position: absolute;
            left: -4px;
            bottom: -4px;
            width: 18px;
            height: 25px;
            transform: rotate(0deg) skewX(-41deg) scale(1.414, 0.707);

            border-bottom-left-radius: 30%;
            background-color: ${theme.color.primary};

            ${orderStep &&
            css`
                display: none;
            `};
        }
    `}
`;

export const FooterBoxInfoTitleStyled = styled.div`
    ${({ theme }) => css`
        margin: 0 0 12px;

        font-size: ${theme.fontSize.bigger};
        font-weight: 700;
        color: ${theme.color.white};

        @media ${theme.mediaQueries.queryLg} {
            flex: 1;
            margin: 0 10px 0 0;

            font-size: 24px;
        }

        @media ${theme.mediaQueries.queryVl} {
            flex: none;
        }
    `}
`;

export const FooterBoxInfoContactStyled = styled.div<FooterBoxInfoStyledProps>`
    ${({ theme, orderStep }) => css`
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 25px;

        @media ${theme.mediaQueries.queryLg} {
            flex: 1;
            margin: 0 10px 0 0;
        }

        @media ${theme.mediaQueries.queryVl} {
            justify-content: center;
        }

        ${orderStep &&
        css`
            margin: 0;
        `};
    `}
`;

export const FooterBoxInfoContactIconStyled = styled(Icon)`
    ${({ theme }) => css`
        margin-right: 12px;
        width: 20px;
        height: 20px;

        color: ${theme.color.orange};
    `}
`;

export const FooterBoxInfoContactPhoneStyled = styled.a<FooterBoxInfoStyledProps>`
    ${({ theme, orderStep }) => css`
        margin-right: 16px;

        font-size: ${theme.fontSize.default};
        font-weight: 700;
        color: ${theme.color.white};
        text-decoration: none;

        @media ${theme.mediaQueries.queryLg} {
            font-size: ${theme.fontSize.bigger};
        }

        &:hover {
            color: ${theme.color.white};
        }

        ${orderStep &&
        css`
            margin-right: 0;

            @media ${theme.mediaQueries.queryLg} {
                margin-right: 16px;
            }
        `};
    `}
`;

export const FooterBoxInfoContactHoursStyled = styled.p<FooterBoxInfoStyledProps>`
    ${({ theme, orderStep }) => css`
        margin: 0;

        font-size: ${theme.fontSize.small};
        color: ${theme.color.white};

        ${orderStep &&
        css`
            display: none;

            @media ${theme.mediaQueries.queryLg} {
                display: block;
            }
        `};
    `}
`;

export const FooterBoxInfoButtonStyled = styled(Button)`
    ${({ theme }) => css`
        z-index: ${theme.zIndex.above};
    `}
`;
