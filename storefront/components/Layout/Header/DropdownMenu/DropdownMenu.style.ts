import { css } from 'styled-components';
import { styled } from 'theme/main';

export const DropdownMenuWrapperStyled = styled.div`
    .dropdown-enter {
        transform: translateY(-110%);
    }
    .dropdown-enter-active {
        transform: translateY(0);
        transition: all 0.5s ease;
    }
    .dropdown-exit {
        transform: translateY(0);
    }
    .dropdown-exit-active {
        transform: translateY(-110%);
        transition: all 0.5s ease;
    }
`;

export const DropdownMenuStyled = styled.div<DropdownMenuProps>`
    ${({ theme, slideTo }) => css`
        position: absolute;
        left: 10px;
        right: 10px;
        top: 0;
        z-index: ${theme.zIndex.menu};
        overflow: hidden;
        cursor: auto;

        background-color: ${theme.color.white};
        box-shadow: 0 5px 10px 0 rgba(164, 167, 193, 0.34);
        transition: height 0.3s ease;

        .menu-primary-enter {
            position: absolute;
            transform: translateX(-110%);
        }

        .menu-primary-enter-active {
            transform: translateX(0%);
            transition: all 0.3s ease;
        }

        .menu-primary-exit {
            position: absolute;
        }

        .menu-primary-exit-active {
            transform: translateX(-110%);
            transition: all 0.3s ease;
        }

        .menu-secondary-enter {
            ${slideTo === 'right'
                ? css`
                      transform: translateX(110%);
                  `
                : css`
                      transform: translateX(-110%);
                      position: absolute;
                  `}
        }

        .menu-secondary-enter-active {
            ${slideTo === 'right'
                ? css`
                      transform: translateX(0%);
                  `
                : css`
                      transform: translateX(0%);
                  `}
            transition: all 0.3s ease;
        }

        .menu-secondary-exit {
            ${slideTo === 'right' &&
            css`
                position: absolute;
                transform: translateX(0%);
            `}
        }

        .menu-secondary-exit-active {
            ${slideTo === 'right'
                ? css`
                      transform: translateX(-110%);
                  `
                : css`
                      transform: translateX(110%);
                  `}
            transition: all 0.3s ease;
        }

        .menu-tertiary-enter {
            ${slideTo === 'right'
                ? css`
                      transform: translateX(110%);
                  `
                : css`
                      transform: translateX(0%);
                  `}
        }
        .menu-tertiary-enter-active {
            ${slideTo === 'right'
                ? css`
                      transform: translateX(0%);
                  `
                : css`
                      transform: translateX(110%);
                  `}
            transition: all 0.3s ease;
        }
        .menu-tertiary-exit {
            ${slideTo === 'left' &&
            css`
                transform: translateX(0%);
            `}
        }
        .menu-tertiary-exit-active {
            transform: translateX(110%);
            transition: all 0.3s ease;
        }
    `}
`;

export const DropdownMenuListStyled = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        width: 100%;
        padding-top: 48px;
    `}
`;

export const DropdownMenuListTitleStyled = styled.div`
    ${({ theme }) => css`
        padding: 0 30px 18px;

        font-size: ${theme.fontSize.default};
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid ${theme.color.greyLighter};
    `}
`;
