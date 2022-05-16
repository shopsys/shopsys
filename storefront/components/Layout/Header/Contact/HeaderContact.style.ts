import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const HeaderContactStyled = styled.div`
    display: flex;
    margin-left: auto;
    order: 2;
`;

export const ContactWrapperStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: column;
        flex: 1;
        align-items: flex-start;
        padding: 14px 0 14px 14px;

        background-color: ${theme.color.primary};

        @media ${theme.mediaQueries.queryLg} {
            align-items: center;
            flex-direction: row;
            justify-content: space-between;
        }
    `}
`;

export const ContactContentStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-wrap: wrap;
        align-items: center;

        @media ${theme.mediaQueries.queryLg} {
            flex: 1;
        }
        @media ${theme.mediaQueries.queryXl} {
            justify-content: center;
        }
    `}
`;

export const PhoneNumberStyled = styled.a`
    ${({ theme }) => css`
        text-decoration: none;

        font-weight: 700;
        font-size: ${theme.fontSize.default};
        color: ${theme.color.creamWhite};

        @media ${theme.mediaQueries.queryLg} {
            margin-right: 16px;
        }
    `}
`;

export const ContactHours = styled.p`
    ${({ theme }) => css`
        margin: 0;

        font-size: ${theme.fontSize.small};
        color: ${theme.color.creamWhite};
    `}
`;

export const HeaderContactIconStyled = styled(Icon)`
    ${({ theme }) => css`
        margin-right: 12px;
        width: 20px;
        height: 20px;

        color: ${theme.color.orange};
    `}
`;
