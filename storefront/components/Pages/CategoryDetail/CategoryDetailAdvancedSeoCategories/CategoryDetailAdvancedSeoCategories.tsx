import 'keen-slider/keen-slider.min.css';
import {
    CategoryDetailAdvancedSeoCategoriesItemStyled as AdvancedSeoCategoriesItemStyled,
    CategoryDetailAdvancedSeoCategoriesWrapperStyled as AdvancedSeoCategoriesWrapperStyled,
} from './CategoryDetailAdvancedSeoCategories.style';
import { FC, useState } from 'react';
import AdvancedSeoCategoriesSlider from './CategoryDetailAdvancedSeoCategoriesSlider';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import Heading from 'components/Basic/Heading';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import NextLink from 'next/link';
import { ReadyCategorySeoMixLink } from 'components/Pages/CategoryDetail/types';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type CategoryDetailAdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
};

const CategoryDetailAdvancedSeoCategories: FC<CategoryDetailAdvancedSeoCategoriesProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { width } = useGetWindowSize();
    const [isAdvancedSeoCategoriesSliderVisible, setAdvancedSeoCategoriesSliderVisibility] = useState(true);
    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setAdvancedSeoCategoriesSliderVisibility(false),
        () => setAdvancedSeoCategoriesSliderVisibility(true),
        () => setAdvancedSeoCategoriesSliderVisibility(isElementVisible([{ min: 0, max: 768 }], width)),
    );

    if (props.readyCategorySeoMixLinks.length === 0) {
        return null;
    }

    return (
        <>
            <Heading type="h3">{t('Favorite categories')}</Heading>
            {isAdvancedSeoCategoriesSliderVisible ? (
                <AdvancedSeoCategoriesSlider readyCategorySeoMixLinks={props.readyCategorySeoMixLinks} />
            ) : (
                <AdvancedSeoCategoriesWrapperStyled>
                    {props.readyCategorySeoMixLinks.map((seoMixLink, index) => (
                        <NextLink key={index} href={seoMixLink.slug} passHref>
                            <AdvancedSeoCategoriesItemStyled>{seoMixLink.name}</AdvancedSeoCategoriesItemStyled>
                        </NextLink>
                    ))}
                </AdvancedSeoCategoriesWrapperStyled>
            )}
        </>
    );
};

export default CategoryDetailAdvancedSeoCategories;
