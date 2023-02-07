import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    SelectedParametersListItemGap: '6px',
} as const;

export const SelectedParametersStyled = styled.div(
    ({ theme }) => css`
        padding: 28px 14px 14px;
        z-index: ${theme.zIndex.aboveOverlay};

        background-color: ${theme.color.blueLight};

        @media ${theme.mediaQueries.queryVl} {
            z-index: 0;
            margin-bottom: 20px;
            border-radius: ${theme.radius.big};
        }

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            border-bottom: 1px solid ${theme.color.greyLight};
        }
    `,
);

export const SelectedParametersTitleStyled = styled(Heading)`
    text-transform: uppercase;
`;

export const SelectedParametersBlockStyled = styled.div`
    margin: 0 0 14px -${localVariables.SelectedParametersListItemGap};
`;

export const SelectedParametersListStyled = styled.ul`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
`;

export const SelectedParametersListItemStyled = styled.li(
    ({ theme }) => css`
        margin-bottom: ${localVariables.SelectedParametersListItemGap};
        margin-left: ${localVariables.SelectedParametersListItemGap};
        padding: 6px 10px;

        background-color: ${theme.color.creamWhite};
        border-radius: ${theme.radius.small};
        color: ${theme.color.base};
        font-size: ${theme.fontSize.small};
    `,
);

export const SelectedParametersListItemRemoveStyled = styled(Icon)`
    width: 13px;
    height: 13px;
    margin-left: 10px;
    transform: translateY(2px);

    cursor: pointer;
`;

export const SelectedParametersResetStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        align-items: center;

        cursor: pointer;
        color: ${theme.color.greyLight};
        font-size: ${theme.fontSize.small};
        text-decoration: none;
    `,
);

export const SelectedParametersResetTextStyled = styled.div`
    font-weight: 700;
    text-transform: uppercase;
`;

export const SelectedParametersResetRemoveStyled = styled(Icon)(
    ({ theme }) => css`
        margin-left: 8px;

        cursor: pointer;
        color: ${theme.color.greyLight};
    `,
);

export const SelectedParametersNameStyled = styled.p(
    ({ theme }) => css`
        margin-bottom: ${localVariables.SelectedParametersListItemGap};
        margin-left: ${localVariables.SelectedParametersListItemGap};
        padding: 6px 0;

        font-size: ${theme.fontSize.small};
    `,
);
