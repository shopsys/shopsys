import {
    CategoryDetailAdvancedSeoCategoriesItemStyled as AdvancedSeoCategoriesItemStyled,
    CategoryDetailAdvancedSeoCategoriesWrapperStyled as AdvancedSeoCategoriesWrapperStyled,
} from './AdvancedSeoCategories.style';
import { AdvancedSeoCategoriesSlider } from './AdvancedSeoCategoriesSlider/AdvancedSeoCategoriesSlider';
import { Heading } from 'components/Basic/Heading/Heading';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import 'keen-slider/keen-slider.min.css';
import NextLink from 'next/link';
import { FC, useState } from 'react';
import { ReadyCategorySeoMixLink } from 'types/category';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
};

export const AdvancedSeoCategories: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
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

    if (readyCategorySeoMixLinks.length === 0) {
        return null;
    }

    return (
        <>
            <Heading type="h3">{t('Favorite categories')}</Heading>
            {isAdvancedSeoCategoriesSliderVisible ? (
                <AdvancedSeoCategoriesSlider readyCategorySeoMixLinks={readyCategorySeoMixLinks} />
            ) : (
                <AdvancedSeoCategoriesWrapperStyled>
                    {readyCategorySeoMixLinks.map((seoMixLink, index) => (
                        <NextLink key={index} href={seoMixLink.slug} passHref>
                            <AdvancedSeoCategoriesItemStyled>{seoMixLink.name}</AdvancedSeoCategoriesItemStyled>
                        </NextLink>
                    ))}
                </AdvancedSeoCategoriesWrapperStyled>
            )}
        </>
    );
};
