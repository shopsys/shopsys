import { Link } from 'components/Basic/Link/Link';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const InfoBoxStyled = styled.div(
    ({ theme }) => css`
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: ${theme.zIndex.above};
        padding: 15px 40px;

        background-color: ${theme.color.white};
        text-align: center;

        @media ${theme.mediaQueries.queryMobile} {
            display: none;
        }
    `,
);

export const LinkStyled = styled(Link)`
    margin-top: 20px;
`;
