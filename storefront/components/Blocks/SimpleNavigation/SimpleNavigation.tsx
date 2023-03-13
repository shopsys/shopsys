import { ListItem } from './ListItem';
import { Slider } from './Slider';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useState } from 'react';
import { ListedItemPropType } from 'types/simpleNavigation';

type SimpleNavigationProps = {
    listedItems: ListedItemPropType[];
    imageType?: string;
};

const TEST_IDENTIFIER = 'blocks-simplenavigation';

export const SimpleNavigation: FC<SimpleNavigationProps> = ({ listedItems, imageType, className }) => {
    const { width } = useGetWindowSize();
    const [isSliderVisible, setSliderVisible] = useState(true);
    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setSliderVisible(false),
        () => setSliderVisible(true),
        () => setSliderVisible(isElementVisible([{ min: 0, max: 768 }], width)),
    );

    return (
        <ul className={className}>
            {isSliderVisible ? (
                <Slider listedItems={listedItems} />
            ) : (
                <ul className="-mb-3 -ml-6 flex flex-wrap p-0" data-testid={TEST_IDENTIFIER}>
                    {listedItems.map((listedItem, key) => (
                        <li
                            className="mb-4 ml-0 pl-0 text-center lg:w-1/2 lg:pl-6 lg:text-left vl:w-1/3 xl:w-1/4"
                            key={key}
                            data-testid={TEST_IDENTIFIER + '-' + key}
                        >
                            <ListItem listedItem={listedItem} imageType={imageType}>
                                {listedItem.name}
                            </ListItem>
                        </li>
                    ))}
                </ul>
            )}
        </ul>
    );
};
