import 'keen-slider/keen-slider.min.css';
import { BannersSliderItemStyled } from './BannersSliderItem.style';
import { FC } from 'react';

type BannersSliderItemProps = {
    link: string;
    imageUrl: string;
};

const BannersSliderItem: FC<BannersSliderItemProps> = (props) => {
    return (
        <a href={props.link} className="keen-slider__slide">
            <BannersSliderItemStyled sliderItemImageUrl={props.imageUrl} />
        </a>
    );
};

export default BannersSliderItem;
