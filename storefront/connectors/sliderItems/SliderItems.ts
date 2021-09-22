import { SliderItem } from './types';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';

export const sliderItemsQuery = `
query sliderItems {
    sliderItems {
        uuid
        name
        link
        extendedText
        extendedTextLink
        images (type: "web", size: "default") {
            type
            position
            size
            url
            width
            height
        }
    }
}
    ` as const;

export const getSliderItems = (): SliderItem[] | undefined => {
    const result = useFetchQuery({ query: sliderItemsQuery });
    return result?.data?.sliderItems;
};
