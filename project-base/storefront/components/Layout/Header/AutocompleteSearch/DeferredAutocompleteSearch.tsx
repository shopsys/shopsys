import { SkeletonModuleAutocompleteSearch } from 'components/Blocks/Skeleton/SkeletonModuleAutocompleteSearch';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import type { AutocompleteSearchProps } from './AutocompleteSearch';

const AutocompleteSearch = dynamic<AutocompleteSearchProps>(
    () => import('./AutocompleteSearch').then((component) => component.AutocompleteSearch),
    {
        ssr: false,
        loading: () => <SkeletonModuleAutocompleteSearch />,
    },
);

type DeferredAutocompleteSearchProps = AutocompleteSearchProps & {
    isDesktop: boolean | undefined;
};

export const DeferredAutocompleteSearch: FC<DeferredAutocompleteSearchProps> = ({
    isDesktop,
    inputRef,
    popupClassName,
    shouldFocusOnMount,
    shouldOpenPopupOnMount,
    shouldRenderResultsOverlay,
}) => {
    const shouldRender = useDeferredRender('autocomplete_search');

    return shouldRender && isDesktop ? (
        <AutocompleteSearch
            inputRef={inputRef}
            popupClassName={popupClassName}
            shouldFocusOnMount={shouldFocusOnMount}
            shouldOpenPopupOnMount={shouldOpenPopupOnMount}
            shouldRenderResultsOverlay={shouldRenderResultsOverlay}
        />
    ) : (
        <SkeletonModuleAutocompleteSearch />
    );
};
