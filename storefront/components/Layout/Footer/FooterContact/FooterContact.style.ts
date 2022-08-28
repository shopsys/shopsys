import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    footerConstactStyledBorderColor: '#4f505c',
    footerConstactStyledMenuItemgap: '20px',
    footerConstactLangsLinkGap: '60px',
    footerConstactLangsLinkGapBottom: '10px',
    footerConstactWidth: '27%',
} as const;

export const FooterContactStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: column;
        align-items: flex-start;

        @media ${theme.mediaQueries.queryLg} {
            align-items: center;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: ${localVariables.footerConstactWidth};
            padding-left: ${localVariables.footerConstactStyledMenuItemgap};
            align-items: flex-start;
        }
    `,
);

export const FooterContactHeadingStyled = styled(Heading)(
    ({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding: 0;
        pointer-events: none;

        cursor: pointer;
        color: ${theme.color.white};
        text-transform: uppercase;
    `,
);

export const FooterContactSocialsStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        overflow: hidden;
        height: 92px;
        width: 100%;
        max-width: 400px;
        margin-bottom: 24px;

        border: 2px solid ${localVariables.footerConstactStyledBorderColor};
        border-radius: ${theme.radius.big};
    `,
);

export const FooterContactInstagramIconStyled = styled(Icon)(
    ({ theme }) => css`
        width: 32px;
        height: 32px;

        color: ${theme.color.white};
    `,
);

export const FooterContactYoutubeIconStyled = styled(Icon)`
    width: 45px;
    height: 45px;

    color: #d93738;
`;

export const FooterContactSocialsItemStyled = styled.a`
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: calc(100% / 3);

    border-left: 2px solid ${localVariables.footerConstactStyledBorderColor};

    &:first-child {
        border-left: 0;
    }
`;

export const FooterContactLangsStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        margin-left: calc(-${localVariables.footerConstactLangsLinkGap} + 20px);
        margin-bottom: -${localVariables.footerConstactLangsLinkGapBottom};

        @media ${theme.mediaQueries.queryLg} {
            margin-left: -${localVariables.footerConstactLangsLinkGap};
        }
    `,
);

export const FooterContactLangsItemStyled = styled.a(
    ({ theme }) => css`
        display: flex;
        align-items: center;
        padding-left: calc(${localVariables.footerConstactLangsLinkGap} - 20px);
        margin-bottom: ${localVariables.footerConstactLangsLinkGapBottom};

        @media ${theme.mediaQueries.queryLg} {
            padding-left: ${localVariables.footerConstactLangsLinkGap};
        }

        &:hover {
            color: ${theme.color.greyLight};
            text-decoration: underline;
        }
    `,
);

export const FooterContactLangsItemTextStyled = styled.span(
    ({ theme }) => css`
        margin-left: 10px;

        color: ${theme.color.greyLight};
        font-size: ${theme.fontSize.small};
    `,
);
