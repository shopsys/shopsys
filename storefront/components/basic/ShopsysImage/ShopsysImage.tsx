import { FC } from 'react';
import Image from 'next/image';
import { ImageType } from './types';
import noImage from '../../../public/images/optimized-noimage.png';

type ShopsysImageProps = { image: ImageType | null; alt: string };

const ShopsysImage: FC<ShopsysImageProps> = (props) => {
    if (props.image === null) {
        return <Image src={noImage} alt={props.alt} layout={'fill'} />;
    }

    return (
        <Image
            src={{
                src: props.image.url,
                height: props.image.height,
                width: props.image.width,
            }}
            alt={props.alt}
        />
    );
};

export default ShopsysImage;
