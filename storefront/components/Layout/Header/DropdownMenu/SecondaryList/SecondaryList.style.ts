import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const SecondaryListTitleStyled = styled.div`
    ${({ theme }) => css`
        padding: 0 30px 18px;

        font-size: ${theme.fontSize.default};
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid ${theme.color.greyLighter};
    `}
`;
