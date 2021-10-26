import { css } from 'styled-components';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import Link from 'components/Basic/Link';
import { styled } from 'components/Theme/main';

const localVariables = {
    ButtonCloseColor: '#4c5bfd',
    ButtonCloseHoverColor: '#dea700',
};

export const InfoBoxStyled = styled.div`
    ${({ theme }) => css`
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
    `}
`;

export const HeadingStyled = styled(Heading)`
    margin: 0 0 10px;
`;

export const HeadingOpeningHoursStyled = styled(Heading)`
    margin: 10px 0 0;
`;

export const LinkStyled = styled(Link)`
    margin-top: 20px;
`;

export const ButtonCloseStyled = styled(Icon)`
    ${({ theme }) => css`
        position: absolute;
        top: 15px;
        right: 15px;
        width: 22px;
        height: 22px;
        cursor: pointer;
        transition: ${theme.transition} color;

        color: ${localVariables.ButtonCloseColor};

        &:hover {
            color: ${localVariables.ButtonCloseHoverColor};
        }
    `}
`;
