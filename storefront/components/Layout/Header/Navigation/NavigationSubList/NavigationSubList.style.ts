import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const NavigationSubListStyled = styled.ul`
    display: flex;
    flex-direction: column;
    width: 100%;
    padding-left: 0;
`;

export const NavigationSubListItemStyled = styled.li`
    width: 100%;
    margin-bottom: 0;

    &:last-child {
        margin-bottom: 0;
    }
`;

export const NavigationSubListItemLinkStyled = styled.a(
    ({ theme }) => css`
        display: block;
        margin-bottom: 5px;

        text-decoration: none;
        color: ${theme.color.base};
        font-weight: 400;
        font-size: ${theme.fontSize.small};
    `,
);
