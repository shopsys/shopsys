import { BannersSliderItemStyled } from './BannersSliderItem.style';
import 'keen-slider/keen-slider.min.css';
import { FC } from 'react';
import { ImageSizeType } from 'types/image';

type BannersSliderItemProps = {
    link: string;
    image: ImageSizeType | null;
};

export const BannersSliderItem: FC<BannersSliderItemProps> = ({ image, link }) => (
    <a href={link} className="keen-slider__slide">
        {image === null ? (
            <BannersSliderItemStyled src="images/optimized-noimage.png" />
        ) : (
            <picture>
                {image.additionalSizes.map((size) => (
                    <source key={size.url} srcSet={size.url} media={size.media} />
                ))}
                <BannersSliderItemStyled src={image.url} />
            </picture>
        )}
    </a>
);
