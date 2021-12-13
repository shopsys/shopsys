import { css } from 'styled-components';
import { styled } from 'components/Theme/main';
import TextInput from 'components/Forms/TextInput';

type WithIsActiveStyledProps = {
    isActive: boolean;
};

export const SearchStyled = styled.div`
    ${({ theme }) => css`
        height: 48px;

        @media ${theme.mediaQueries.queryLg} {
            position: relative;
        }
    `}
`;

export const SearchInStyled = styled.div`
    ${({ theme }) => css`
        transition: all 0.2s cubic-bezier(0.8, 0.2, 0.48, 1);

        @media ${theme.mediaQueries.queryLg} {
            width: 100%;
            position: absolute;
            left: 0;
            top: 0;
            z-index: ${theme.zIndex.aboveMenu};
        }
    `}
`;

export const SearchFormStyled = styled.form<WithIsActiveStyledProps>`
    ${({ theme, isActive }) => css`
        position: relative;
        display: flex;
        transition: all ${theme.transition};
        width: 100%;

        ${isActive &&
        css`
            z-index: ${theme.zIndex.aboveMenu + 1};
        `};

        & input {
            border: 2px solid ${theme.color.white};
            border-radius: ${theme.radius.big};
        }

        @media ${theme.mediaQueries.queryLg} {
            ${isActive &&
            css`
                width: 576px;
            `}

            &:focus-within {
                width: 576px;
            }
        }

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            & input {
                ${isActive &&
                css`
                    width: 100%;
                    border-color: ${theme.color.primaryLight};
                `}
            }
        }
    `}
`;

export const SearchTextInputStyled = styled(TextInput)`
    width: 100%;
    margin-bottom: 0;

    &::-webkit-search-decoration,
    &::-webkit-search-cancel-button,
    &::-webkit-search-results-button,
    &::-webkit-search-results-decoration {
        -webkit-appearance: none;
    }
`;

export const RemoveSearchButtonStyled = styled.div`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        height: 20px;
        justify-content: center;
        position: absolute;
        right: 46px;
        top: 50%;
        transform: translateY(-50%);
        transition: all ${theme.transition};
        width: 20px;

        background-color: ${theme.color.greyLighter};
        border-radius: 50%;
        cursor: pointer;
    `}
`;
