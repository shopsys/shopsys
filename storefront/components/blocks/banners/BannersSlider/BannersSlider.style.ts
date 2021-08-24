import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

const localVariables = {
    bannersSliderThumbnailControlsWidth: '307px',
} as const;

export const StyledBannersSliderBox = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        display: flex;
        margin-bottom: 52px;
        padding-bottom: 0;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            flex-direction: column;
        }
    `}
`;

export const StyledBannersSlider = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        width: calc(100% - ${localVariables.bannersSliderThumbnailControlsWidth});
        height: 290px;

        cursor: pointer;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            height: 250px;
            width: 100%;
        }

        @media ${theme.mediaQueries.queryTablet} {
            height: 200px;
            width: 100%;
        }
    `}
`;

export const StyledBannersSliderThumbnailControls = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        max-width: ${localVariables.bannersSliderThumbnailControlsWidth};
        min-width: ${localVariables.bannersSliderThumbnailControlsWidth};
        padding: 0 0 0 25px;

        button {
            display: block;
            margin-bottom: 16px;
            padding: 14px 32px;
            position: relative;
            width: 100% !important;

            transition: ${theme.transition};
            border: 2px solid ${theme.color.blueLight};
            text-align: left;
            cursor: pointer;
            background-color: ${theme.color.blueLight};
            border-radius: ${theme.radius.big};
            font-size: ${theme.fontSize.default};
            font-weight: 700;

            &:hover {
                background-color: ${theme.color.blue};
                border: 2px solid ${theme.color.blue};
            }

            &:disabled {
                background-color: ${theme.color.creamWhite};
                border-color: ${theme.color.primary};
            }
        }

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            display: none;
        }
    `}
`;

export const StyledBannersSliderDotControls = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        display: flex;
        justify-content: center;
        margin-top: 15px;

        button {
            margin: 0 4px;
            height: 6px;
            outline: 0;
            width: 12px;

            cursor: pointer;
            border-radius: ${theme.radius.small};
            background-color: ${theme.color.baseLighter};
            font-size: 0;
            border: 0;

            &:disabled {
                background-color: ${theme.color.primary};
            }
        }

        @media ${theme.mediaQueries.queryVl} {
            display: none;
        }
    `}
`;
