import { FC, ImgHTMLAttributes } from 'react';
import { ImageSizeType, ImageType } from 'types/image';
import { CSSProperties } from 'styled-components';
import { Img } from './Image.style';

type ImageProps = {
    image: ImageType | null;
    alt: string;
    type: string;
    loading?: ImgHTMLAttributes<HTMLImageElement>['loading'];
    testId?: string;
    maxWidth?: CSSProperties['maxWidth'];
    maxHeight?: CSSProperties['maxHeight'];
};

const Image: FC<ImageProps> = (props) => {
    const testIdentifier = props.testId ?? 'basic-image';

    const img: ImageSizeType | null = props.image?.sizes?.find((i) => i.size === props.type) ?? null;

    if (img === null) {
        return <img src={'/images/optimized-noimage.png'} alt={props.alt} data-testid={testIdentifier + '-empty'} />;
    }

    return (
        <picture data-testid={testIdentifier}>
            {img.additionalSizes.map((size) => (
                <source key={size.url} srcSet={size.url} media={size.media} />
            ))}
            <Img
                className="responsive-image"
                src={img.url}
                alt={props.alt}
                loading={props.loading}
                maxWidth={props.maxWidth ?? (img.width !== null ? `${img.width}px` : undefined)}
                maxHeight={props.maxHeight ?? (img.height !== null ? `${img.height}px` : undefined)}
            />
        </picture>
    );
};

export default Image;
