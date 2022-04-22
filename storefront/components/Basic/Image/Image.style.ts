import { css, CSSProperties } from 'styled-components';
import { HTMLAttributes } from 'react';
import { styled } from 'components/Theme/main';

type ImageType = HTMLAttributes<HTMLImageElement> & {
    maxHeight?: CSSProperties['maxHeight'];
    maxWidth?: CSSProperties['maxWidth'];
};

export const Img = styled.img<ImageType>(
    ({ maxHeight, maxWidth }) => css`
        display: block;
        max-height: ${maxHeight ?? undefined};
        max-width: ${maxWidth ?? undefined};
        object-fit: contain;
        width: 100%;
    `,
);
