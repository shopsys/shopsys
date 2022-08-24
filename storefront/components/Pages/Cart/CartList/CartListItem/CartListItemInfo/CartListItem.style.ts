import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const NameStyled = styled.div`
    ${({ theme }) => css`
        text-align: left;
        padding-right: 15px;
        height: 100%;

        @media ${theme.mediaQueries.queryVl} {
            width: 270px;
        }
    `}
`;

export const NameTitleStyled = styled.a`
    ${({ theme }) => css`
        line-height: 18px;

        font-size: ${theme.fontSize.small};
        font-weight: 700;
        text-transform: uppercase;
        text-decoration: none;
        color: ${theme.color.base};

        &:hover {
            text-decoration: none;
            color: ${theme.color.base};
        }
    `}
`;

export const NameTitleTextStyled = styled.span`
    margin-right: 5px;
`;

export const CodeStyled = styled.div`
    ${({ theme }) => css`
        line-height: 19px;

        color: ${theme.color.greyLight};
        font-size: 13px;
    `}
`;

export const AvailabilityStyled = styled.div`
    display: block;
    flex: 1;
`;

export const AvailabilityMessageStyled = styled.span`
    ${({ theme }) => css`
        display: block;

        font-weight: 400;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            display: inline;
            margin-left: 5px;
        }
    `}
`;
