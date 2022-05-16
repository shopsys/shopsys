import { BannersSliderItemStyled } from './BannersSliderItem.style';
import 'keen-slider/keen-slider.min.css';
import { FC } from 'react';
import { ImageSizeType } from 'types/image';

type BannersSliderItemProps = {
    link: string;
    image: ImageSizeType | null;
};

const BannersSliderItem: FC<BannersSliderItemProps> = (props) => {
    return (
        <a href={props.link} className="keen-slider__slide">
            {props.image === null ? (
                <BannersSliderItemStyled src="images/optimized-noimage.png" />
            ) : (
                <picture>
                    {props.image.additionalSizes.map((size) => (
                        <source key={size.url} srcSet={size.url} media={size.media} />
                    ))}
                    <BannersSliderItemStyled src={props.image.url} />
                </picture>
            )}
        </a>
    );
};

export default BannersSliderItem;
