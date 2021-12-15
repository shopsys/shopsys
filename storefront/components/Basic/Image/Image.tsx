import { FC } from 'react';
import { ImageType } from './types';
import NextImage from 'next/image';

type ImageProps = {
    image: ImageType | null;
    alt: string;
};

const Image: FC<ImageProps> = (props) => {
    if (props.image === null || props.image === undefined) {
        return <img src={'/images/optimized-noimage.png'} alt={props.alt} />;
    }
    return (
        <NextImage
            src={{
                src: props.image.url,
                height: props.image.height,
                width: props.image.width,
            }}
            alt={props.alt}
            unoptimized={true} // Images are optimized already in backend
        />
    );
};

export default Image;
