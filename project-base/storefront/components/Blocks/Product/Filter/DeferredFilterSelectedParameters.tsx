import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import { FilterSelectedParametersProps } from './FilterSelectedParameters';

const FilterSelectedParameters = dynamic(
    () => import('./FilterSelectedParameters').then((component) => component.FilterSelectedParameters),
    { ssr: false },
);

export const DeferredFilterSelectedParameters: FC<FilterSelectedParametersProps> = (props) => {
    const shouldRender = useDeferredRender('selected_filters');

    return shouldRender ? <FilterSelectedParameters {...props} /> : null;
};
