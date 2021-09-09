import 'keen-slider/keen-slider.min.css';
import { FC } from 'react';
import { StyledBannersSliderItem } from './BannersSliderItem.style';

type BannersSliderItemProps = {
    link: string;
    imageUrl: string;
};

const BannersSliderItem: FC<BannersSliderItemProps> = (props) => {
    return (
        <a href={props.link} className="keen-slider__slide">
            <StyledBannersSliderItem sliderItemImageUrl={props.imageUrl} />
        </a>
    );
};

export default BannersSliderItem;
