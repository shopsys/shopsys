import 'keen-slider/keen-slider.min.css';
import {
    CategoryDetailAdvancedSeoCategoriesItem as AdvancedSeoCategoriesItem,
    CategoryDetailAdvancedSeoCategoriesWrapperStyled as AdvancedSeoCategoriesWrapperStyled,
} from './CategoryDetailAdvancedSeoCategories.style';
import { FC, useEffect, useState } from 'react';
import AdvancedSeoCategoriesSlider from './CategoryDetailAdvancedSeoCategoriesSlider';
import { desktopFirstSizes } from 'theme/mediaQueries';
import Link from 'next/link';
import { ReadyCategorySeoMixLink } from '../types';
import ShopsysHeading from 'components/basic/ShopsysHeading';
import { useGetWindowSize } from 'hooks/UseGetWindowSize';
import { useTranslation } from 'next-i18next';

type CategoryDetailAdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
};

const CategoryDetailAdvancedSeoCategories: FC<CategoryDetailAdvancedSeoCategoriesProps> = (props) => {
    const { t } = useTranslation();
    const [previousWindowWidth, setPreviousWindowWidth] = useState(0);
    const [isAdvancedSeoCategoriesSliderVisible, setAdvancedSeoCategoriesSliderVisibility] = useState(false);
    const { width } = useGetWindowSize();

    useEffect(() => {
        if (previousWindowWidth > desktopFirstSizes.tablet && width <= desktopFirstSizes.tablet) {
            setAdvancedSeoCategoriesSliderVisibility(true);
        }
        if (previousWindowWidth <= desktopFirstSizes.tablet && width > desktopFirstSizes.tablet) {
            setAdvancedSeoCategoriesSliderVisibility(false);
        }
        setPreviousWindowWidth(width);
    }, [width]);

    return (
        <>
            <ShopsysHeading type="h3">{t<string>('Favorite categories')}</ShopsysHeading>
            {isAdvancedSeoCategoriesSliderVisible ? (
                <AdvancedSeoCategoriesSlider readyCategorySeoMixLinks={props.readyCategorySeoMixLinks} />
            ) : (
                <AdvancedSeoCategoriesWrapperStyled>
                    {props.readyCategorySeoMixLinks.map((seoMixLink, index) => (
                        <Link key={index} href={seoMixLink.slug} passHref>
                            <AdvancedSeoCategoriesItem>{seoMixLink.name}</AdvancedSeoCategoriesItem>
                        </Link>
                    ))}
                </AdvancedSeoCategoriesWrapperStyled>
            )}
        </>
    );
};

export default CategoryDetailAdvancedSeoCategories;
