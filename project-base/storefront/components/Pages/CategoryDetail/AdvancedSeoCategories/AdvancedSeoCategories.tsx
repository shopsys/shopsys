import { AdvancedSeoCategoriesItem } from './AdvancedSeoCategoriesItem';
import { AdvancedSeoCategoriesSlider } from './AdvancedSeoCategoriesSlider';
import { Heading } from 'components/Basic/Heading/Heading';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import 'keen-slider/keen-slider.min.css';
import { useState } from 'react';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: CategoryDetailFragmentApi['readyCategorySeoMixLinks'];
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
                <div className="mb-5 flex flex-row flex-wrap gap-3">
                    {readyCategorySeoMixLinks.map((seoMixLink, index) => (
                        <AdvancedSeoCategoriesItem key={index} slug={seoMixLink.slug}>
                            {seoMixLink.name}
                        </AdvancedSeoCategoriesItem>
                    ))}
                </div>
            )}
        </>
    );
};
