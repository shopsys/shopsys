import { ListItem } from 'components/Blocks/SimpleNavigation/ListItem/ListItem';
import { ListItemStyled } from 'components/Blocks/SimpleNavigation/SimpleNavigation.style';
import { theme } from 'components/Theme/main';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { FC } from 'react';
import { ListedItemPropType } from 'types/simpleNavigation';

type SliderProps = {
    listedItems: ListedItemPropType[];
};

export const Slider: FC<SliderProps> = (props) => {
    const testIdentifier = 'blocks-simplenavigation-slider-';

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
                <ListItemStyled key={key} className="keen-slider__slide" data-testid={testIdentifier + key}>
                    <ListItem listedItem={listedItem}>{listedItem.name}</ListItem>
                </ListItemStyled>
            ))}
        </div>
    );
};
