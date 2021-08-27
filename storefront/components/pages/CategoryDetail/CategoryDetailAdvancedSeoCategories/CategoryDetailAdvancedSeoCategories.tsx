import 'keen-slider/keen-slider.min.css';
import {
    CategoryDetailAdvancedSeoCategoriesItemStyled as AdvancedSeoCategoriesItemStyled,
    CategoryDetailAdvancedSeoCategoriesWrapperStyled as AdvancedSeoCategoriesWrapperStyled,
} from './CategoryDetailAdvancedSeoCategories.style';
import { FC, useState } from 'react';
import AdvancedSeoCategoriesSlider from './CategoryDetailAdvancedSeoCategoriesSlider';
import { desktopFirstSizes } from 'theme/mediaQueries';
import { getIsElementVisible } from 'helpers/GetIsItemVisible';
import Link from 'next/link';
import { ReadyCategorySeoMixLink } from '../types';
import ShopsysHeading from 'components/basic/ShopsysHeading';
import { useGetWindowSize } from 'hooks/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/UseResizeWidthEffect';
import { useTranslation } from 'next-i18next';

type CategoryDetailAdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
};

const CategoryDetailAdvancedSeoCategories: FC<CategoryDetailAdvancedSeoCategoriesProps> = (props) => {
    const { t } = useTranslation();
    const { width } = useGetWindowSize();
    const [isAdvancedSeoCategoriesSliderVisible, setAdvancedSeoCategoriesSliderVisibility] = useState(true);
    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setAdvancedSeoCategoriesSliderVisibility(false),
        () => setAdvancedSeoCategoriesSliderVisibility(true),
        () => setAdvancedSeoCategoriesSliderVisibility(getIsElementVisible([{ min: 0, max: 768 }], width)),
    );

    return (
        <>
            <ShopsysHeading type="h3">{t<string>('Favorite categories')}</ShopsysHeading>
            {isAdvancedSeoCategoriesSliderVisible ? (
                <AdvancedSeoCategoriesSlider readyCategorySeoMixLinks={props.readyCategorySeoMixLinks} />
            ) : (
                <AdvancedSeoCategoriesWrapperStyled>
                    {props.readyCategorySeoMixLinks.map((seoMixLink, index) => (
                        <Link key={index} href={seoMixLink.slug} passHref>
                            <AdvancedSeoCategoriesItemStyled>{seoMixLink.name}</AdvancedSeoCategoriesItemStyled>
                        </Link>
                    ))}
                </AdvancedSeoCategoriesWrapperStyled>
            )}
        </>
    );
};

export default CategoryDetailAdvancedSeoCategories;
