import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const FooterMenuStyled = styled.div`
    ${({ theme }) => css`
        margin: 0 -${theme.layout.padding} 30px;

        @media ${theme.mediaQueries.queryLg} {
            display: flex;
            margin-bottom: 40px;
            margin-left: -20px;
        }

        @media ${theme.mediaQueries.queryVl} {
            flex: 1;
            margin-bottom: 0;
        }
    `}
`;
