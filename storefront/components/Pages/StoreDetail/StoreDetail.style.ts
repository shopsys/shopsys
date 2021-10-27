import { css } from 'styled-components';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

export const StoreDetailMapSize = {
    small: '350px',
    bigger: '500px',
    big: '650px',
};

type ButtonBottomNameProps = {
    type?: 'right';
};

type ButtonBottomIconProps = {
    type?: 'right';
};

export const StoreDetailStyled = styled.div`
    margin-bottom: 40px;
`;

export const StoreDetailContentStyled = styled.div`
    ${({ theme }) => css`
        position: relative;

        @media ${theme.mediaQueries.queryLg} {
            min-height: ${StoreDetailMapSize.small};
            padding-left: calc(${StoreDetailMapSize.small} + 30px);
        }

        @media ${theme.mediaQueries.queryVl} {
            min-height: ${StoreDetailMapSize.bigger};
            padding-left: calc(${StoreDetailMapSize.bigger} + 30px);
        }

        @media ${theme.mediaQueries.queryXl} {
            min-height: ${StoreDetailMapSize.big};
            padding-left: calc(${StoreDetailMapSize.big} + 70px);
        }
    `}
`;

export const InfoStyled = styled.div`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryMd} {
            display: flex;
            flex-wrap: wrap;
        }
    `}
`;

export const InfoItemStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 16px;

        @media ${theme.mediaQueries.queryMd} {
            width: 50%;
            margin-bottom: 24px;

            &:nth-child(odd) {
                padding-right: 10px;
            }

            &:nth-child(even) {
                padding-left: 10px;
            }
        }
    `}
`;

export const InfoItemSubtitleStyled = styled(Heading)`
    ${({ theme }) => css`
        display: block;
        margin-bottom: 4px;

        color: ${theme.color.primary};
        font-weight: 400;
    `}
`;

export const InfoItemOpeningHoursStyled = styled.div`
    max-width: 160px;
`;

export const ButtonBottomStyled = styled.a`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 16px 16px 24px;

        border-radius: ${theme.radius.big};
        border: 1px solid ${theme.color.greyLighter};
        transition: all ${theme.transition};

        &:hover {
            text-decoration: none;

            @media ${theme.mediaQueries.queryVl} {
                transform: translateY(-4px);
                box-shadow: 0 10px 20px 5px ${theme.color.greyLighter};
            }
        }
    `}
`;

export const ButtonBottomItemStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: row;
        align-items: center;

        color: ${theme.color.primary};
        font-size: ${theme.fontSize.bigger};
    `}
`;

export const ButtonBottomNameStyled = styled.div<ButtonBottomNameProps>`
    ${({ theme, type }) => css`
        position: relative;
        flex-grow: 1;

        color: ${theme.color.primary};
        font-size: ${theme.fontSize.default};
        font-weight: 400;

        @media ${theme.mediaQueries.queryMd} {
            font-size: ${theme.fontSize.bigger};
        }

        ${type === 'right' &&
        css`
            display: none;

            @media ${theme.mediaQueries.queryVl} {
                display: block;
                margin-left: 20px;
            }
        `}
    `}
`;

export const ButtonBottomIconStyled = styled(Icon)<ButtonBottomIconProps>`
    ${({ theme, type }) => css`
        width: 24px;
        height: 24px;
        ${type === 'right' ? 'margin-left' : 'margin-right'}: 12px;

        font-size: 24px;
        color: ${type === 'right' ? `${theme.color.primary}` : `${theme.color.orange}`};

        @media ${theme.mediaQueries.queryXl} {
            ${type === 'right' ? 'margin-left' : 'margin-right'}: 20px;
        }
    `}
`;
