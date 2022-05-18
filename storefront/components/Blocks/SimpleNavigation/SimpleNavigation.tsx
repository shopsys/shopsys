import ListItem from './ListItem';
import { ListItemStyled, SimpleNavigationStyled } from './SimpleNavigation.style';
import Slider from './Slider';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { FC, useState } from 'react';
import { ListedItemPropType } from 'types/simpleNavigation';

type SimpleNavigationProps = {
    listedItems: ListedItemPropType[];
    imageType?: string;
};

const SimpleNavigation: FC<SimpleNavigationProps> = (props) => {
    const testIdentifier = 'blocks-simplenavigation';

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
        <ul>
            {isSliderVisible ? (
                <Slider listedItems={props.listedItems} />
            ) : (
                <SimpleNavigationStyled data-testid={testIdentifier}>
                    {props.listedItems.map((listedItem, key) => (
                        <ListItemStyled key={key} data-testid={testIdentifier + '-' + key}>
                            <ListItem listedItem={listedItem} imageType={props.imageType}>
                                {listedItem.name}
                            </ListItem>
                        </ListItemStyled>
                    ))}
                </SimpleNavigationStyled>
            )}
        </ul>
    );
};

export default SimpleNavigation;
