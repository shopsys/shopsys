import 'keen-slider/keen-slider.min.css';
import {
    CategoryDetailAdvancedSeoCategoriesItemStyled as AdvancedSeoCategoriesItemStyled,
    CategoryDetailAdvancedSeoCategoriesWrapperStyled as AdvancedSeoCategoriesWrapperStyled,
} from './CategoryDetailAdvancedSeoCategories.style';
import { FC, useState } from 'react';
import AdvancedSeoCategoriesSlider from './CategoryDetailAdvancedSeoCategoriesSlider';
import { desktopFirstSizes } from '../../../Theme/mediaQueries';
import { getIsElementVisible } from 'components/Helpers/GetIsItemVisible';
import Heading from 'components/Basic/Heading';
import NextLink from 'next/link';
import { ReadyCategorySeoMixLink } from '../types';
import { useGetWindowSize } from 'hooks/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/UseResizeWidthEffect';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

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
        () => setAdvancedSeoCategoriesSliderVisibility(getIsElementVisible([{ min: 0, max: 768 }], width)),
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
