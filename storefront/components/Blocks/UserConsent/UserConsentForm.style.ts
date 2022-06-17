import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ConsentRowStyled = styled.div(
    ({ theme }) => css`
        margin: 6px 0;
        display: flex;
        justify-content: space-between;

        border-bottom: 1px solid ${theme.color.greyLight};
    `,
);

export const ConsentNameStyled = styled.span`
    font-size: 20px;
`;

export const ConsentButtonsRowStyled = styled.div`
    display: flex;
    justify-content: end;
    margin: 40px 0 20px 0;
    column-gap: 10px;
    row-gap: 10px;
    flex-wrap: wrap;
`;
