import { Icon } from 'components/Basic/Icon/Icon';
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

export const FilterGroupStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: -${localVariables.filterGroupBorderWidth};
        border-bottom: ${localVariables.filterGroupBorderWidth} solid ${theme.color.border};
    `}
`;

export const FilterGroupTitleStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        position: relative;
        padding: 25px 20px 25px 0;
        margin: 0;

        text-transform: uppercase;
        color: ${theme.color.black};
        font-size: ${theme.fontSize.default};
        font-weight: 700;
        cursor: pointer;
    `}
`;

export const FilterGroupContentStyled = styled.div<FilterGroupStyledProps>`
    ${({ isOpen }) => css`
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
    `}
`;

export const FilterGroupContentItemStyled = styled.div<FilterGroupContentItemStyledProps>`
    ${({ isDisabled, isActive }) => css`
        margin-bottom: 10px;
        ${isDisabled &&
        !isActive &&
        css`
            opacity: 0.3;
            pointer-events: none;
        `}
    `}
`;

export const FilterGroupColorStyled = styled.div`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
`;

export const FilterGroupArrowStyled = styled(Icon)<FilterGroupStyledProps>`
    ${({ theme, isOpen }) => css`
        position: absolute;
        right: 0;
        top: 50%;

        font-size: 12px;
        user-select: none;
        transform: translateY(-50%) rotate(0deg);
        transition: ${theme.transition};

        ${isOpen
            ? css`
                  transform: translateY(-50%) rotate(180deg);
                  transition: ${theme.transition};
              `
            : css`
                  transform: translateY(-50%) rotate(0deg);
                  transition: ${theme.transition};
              `};
    `}
`;
