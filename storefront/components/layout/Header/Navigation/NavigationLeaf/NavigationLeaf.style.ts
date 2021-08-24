import styled from 'styled-components';

const localVariables = {
    navigationSubListItemGap: '45px',
} as const;

export const NavigationLeafColumnStyled = styled.ul`
    display: flex;
    flex-direction: column;
    width: calc(100% / 4);
    padding-left: ${localVariables.navigationSubListItemGap};
`;
