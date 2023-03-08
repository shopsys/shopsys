import { ListItem } from 'components/Blocks/SimpleNavigation/ListItem/ListItem';
import { theme } from 'components/Theme/main';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import { ListedItemPropType } from 'types/simpleNavigation';

type SliderProps = {
    listedItems: ListedItemPropType[];
};

const TEST_IDENTIFIER = 'blocks-simplenavigation-slider-';

export const Slider: FC<SliderProps> = ({ listedItems }) => {
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
            {listedItems.map((listedItem, key) => (
                <li
                    key={key}
                    className="keen-slider__slide mb-4 ml-0 pl-0 text-center lg:w-1/2 lg:pl-6 lg:text-left vl:w-1/3 xl:w-1/4"
                    data-testid={TEST_IDENTIFIER + key}
                >
                    <ListItem listedItem={listedItem}>{listedItem.name}</ListItem>
                </li>
            ))}
        </div>
    );
};
