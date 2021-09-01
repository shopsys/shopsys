import { css } from 'styled-components';
import { styled } from 'theme/main';

const localVariables = {
    bannersSliderThumbnailControlsWidth: '307px',
} as const;

type StyledBannersSliderItemProps = {
    sliderItemImageUrl: string;
    sliderItemImageHeight: number;
};

export const StyledBannersSliderBox = styled.div`
    ${({ theme }) => css`
        display: flex;
        margin-bottom: 52px;
        padding-bottom: 0;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            flex-direction: column;
        }
    `}
`;

export const StyledBannersSlider = styled.div`
    ${({ theme }) => css`
        width: calc(100% - 307px);

        cursor: pointer;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            width: 100%;
        }
    `}
`;

export const StyledBannersSliderItem = styled.div<StyledBannersSliderItemProps>`
    ${({ theme, sliderItemImageUrl, sliderItemImageHeight }) => css`
        height: ${`${sliderItemImageHeight}px`};

        background: ${`url(${sliderItemImageUrl}) center  no-repeat`};
        border-radius: ${theme.radius.big};
    `}
`;

export const StyledBannersSliderThumbnailControls = styled.div`
    ${({ theme }) => css`
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
    ${({ theme }) => css`
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
            background-color: ${theme.color.greyLight};
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
