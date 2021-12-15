import { FC, useState } from 'react';
import { ListItemStyled, SimpleNavigationStyled } from './SimpleNavigation.style';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { ListedItemPropType } from 'types/simpleNavigation';
import ListItem from './ListItem';
import Slider from './Slider';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';

type SimpleNavigationProps = {
    listedItems: ListedItemPropType[];
};

const SimpleNavigation: FC<SimpleNavigationProps> = (props) => {
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
                <SimpleNavigationStyled>
                    {props.listedItems.map((listedItem, key) => (
                        <ListItemStyled key={key}>
                            <ListItem listedItem={listedItem}>{listedItem.name}</ListItem>
                        </ListItemStyled>
                    ))}
                </SimpleNavigationStyled>
            )}
        </ul>
    );
};

export default SimpleNavigation;
