import styled, { css } from 'styled-components';

export const StyledShopsysLink = styled.a`
    ${({ theme }) => css`
        display: inline-flex;
        align-items: center;

        color: ${theme.color.grey};
        text-decoration: underline;
        cursor: pointer;
        outline: none;
        background-color: transparent;

        &:hover {
            color: ${theme.color.primary};
        }
    `}
`;

export const StyledShopsysLinkIcon = styled.img`
    width: 16px;
    margin-right: 15px;
`;
