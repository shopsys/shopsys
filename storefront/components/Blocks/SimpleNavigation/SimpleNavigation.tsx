import { ListItem } from './ListItem/ListItem';
import { ListItemStyled, SimpleNavigationStyled } from './SimpleNavigation.style';
import { Slider } from './Slider/Slider';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { FC, HTMLAttributes, useState } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { ListedItemPropType } from 'types/simpleNavigation';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'className'>;

type SimpleNavigationProps = NativeProps & {
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
                <SimpleNavigationStyled data-testid={TEST_IDENTIFIER}>
                    {listedItems.map((listedItem, key) => (
                        <ListItemStyled key={key} data-testid={TEST_IDENTIFIER + '-' + key}>
                            <ListItem listedItem={listedItem} imageType={imageType}>
                                {listedItem.name}
                            </ListItem>
                        </ListItemStyled>
                    ))}
                </SimpleNavigationStyled>
            )}
        </ul>
    );
};
