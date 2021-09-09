import { FC } from 'react';
import Image from 'next/image';
import { ImageType } from './types';

type ShopsysImageProps = { image: ImageType | null; alt: string };

const ShopsysImage: FC<ShopsysImageProps> = (props) => {
    if (props.image === null) {
        return <Image src={'/images/optimized-noimage.png'} alt={props.alt} layout={'fill'} />;
    }

    return (
        <Image
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

export default ShopsysImage;
