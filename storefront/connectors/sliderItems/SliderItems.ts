import { SliderItemFragmentApi, useSliderItemsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const useSliderItems = (): SliderItemFragmentApi[] | undefined => {
    const [{ data, error }] = useSliderItemsQueryApi();
    useQueryError(error);

    return data?.sliderItems;
};
