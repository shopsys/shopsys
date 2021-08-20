import styled, { css } from 'styled-components';

type NavigationLeafProps = {
    isChildren?: boolean;
};

const localVariables = {
    navigationSubListItemGap: '45px',
} as const;

export const NavigationLeafColumnStyled = styled.ul<NavigationLeafProps>`
    ${({ isChildren }: NavigationLeafProps) => css`
        display: flex;
        flex-direction: column;
        width: calc(100% / 4);
        padding-left: ${localVariables.navigationSubListItemGap};

        ${isChildren &&
        css`
            padding-left: 0;
        `}
    `}
`;
