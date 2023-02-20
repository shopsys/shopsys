import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    filterGroupBorderWidth: '1px',
} as const;

type FilterGroupStyledProps = {
    isOpen?: boolean;
};

type FilterGroupContentItemStyledProps = {
    isDisabled: boolean;
    isActive: boolean;
};

export const FilterGroupStyled = styled.div(
    ({ theme }) => css`
        margin-bottom: -${localVariables.filterGroupBorderWidth};
        border-bottom: ${localVariables.filterGroupBorderWidth} solid ${theme.color.border};
    `,
);

export const FilterGroupTitleStyled = styled.div(
    ({ theme }) => css`
        display: block;
        position: relative;
        padding: 25px 20px 25px 0;
        margin: 0;

        text-transform: uppercase;
        color: ${theme.color.black};
        font-size: ${theme.fontSize.default};
        font-weight: 700;
        cursor: pointer;
    `,
);

export const FilterGroupContentStyled = styled.div<FilterGroupStyledProps>(
    ({ isOpen }) => css`
        flex-wrap: wrap;
        flex-direction: column;
        margin-bottom: 24px;

        ${isOpen
            ? css`
                  display: flex;
              `
            : css`
                  display: none;
              `};
    `,
);

export const FilterGroupContentItemStyled = styled.div<FilterGroupContentItemStyledProps>(
    ({ isDisabled, isActive }) => css`
        margin-bottom: 10px;
        ${isDisabled &&
        !isActive &&
        css`
            opacity: 0.3;
            pointer-events: none;
        `}
    `,
);

export const FilterGroupColorStyled = styled.div`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
`;

export const ShowAllButtonStyled = styled.button(
    ({ theme }) => css`
        width: fit-content;
        padding: 0;

        font-size: ${theme.fontSize.small};
        border: 0;
        background: none;
        outline: 0;
        color: ${theme.color.black};
        cursor: pointer;
        text-decoration: underline;

        &:hover {
            text-decoration: none;
            background: none;
            color: ${theme.color.primary};
        }
    `,
);
