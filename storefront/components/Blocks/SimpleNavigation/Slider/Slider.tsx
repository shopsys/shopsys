import 'keen-slider/keen-slider.min.css';
import { FC } from 'react';
import { ListedItemPropType } from 'components/Blocks/SimpleNavigation/types';
import ListItem from 'components/Blocks/SimpleNavigation/ListItem';
import { ListItemStyled } from 'components/Blocks/SimpleNavigation/SimpleNavigation.style';
import { theme } from 'components/Theme/main';
import { useKeenSlider } from 'keen-slider/react';

type SliderProps = {
    listedItems: ListedItemPropType[];
};

const Slider: FC<SliderProps> = (props) => {
    const [sliderRef] = useKeenSlider<HTMLDivElement>({
        breakpoints: {
            [theme.mediaQueries.queryTablet]: {
                slidesPerView: 5.3,
                spacing: 10,
            },
            [theme.mediaQueries.queryMobile]: {
                slidesPerView: 4.3,
                spacing: 10,
            },
            [theme.mediaQueries.queryMobileXs]: {
                slidesPerView: 2.5,
                spacing: 10,
            },
        },
    });

    return (
        <div ref={sliderRef} className="keen-slider">
            {props.listedItems.map((listedItem, key) => (
                <ListItemStyled key={key} className="keen-slider__slide">
                    <ListItem listedItem={listedItem}>{listedItem.name}</ListItem>
                </ListItemStyled>
            ))}
        </div>
    );
};

export default Slider;
