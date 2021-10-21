import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const FooterCopyrightStyled = styled.div`
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
`;

export const FooterCopyrightTextStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        margin: 0;

        color: ${theme.color.greyLight};
        font-size: 13px;
    `}
`;

export const FooterCopyrightLogoStyled = styled.a`
    display: flex;
    width: 77px;
    margin-left: 7px;
`;
