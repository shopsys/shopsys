import { styled } from 'components/Theme/main';
import { CSSProperties, HTMLAttributes } from 'react';
import { css } from 'styled-components';

type IconSvgStyleProps = HTMLAttributes<HTMLElement> & {
    $width: number;
    $height: number;
    $color?: CSSProperties['color'];
};

export const IconSvgStyled = styled.i<IconSvgStyleProps>(
    ({ theme, $width, $height, $color = theme.color.base }) => css`
        display: inline-flex;
        line-height: 0;
        width: ${$width}px;
        height: ${$height}px;
        text-align: center;

        color: ${$color};
        font-style: normal;
        text-transform: none;

        svg,
        img {
            vertical-align: top;
            width: 100%;
            height: 100%;

            font-size: inherit;
        }
    `,
);
