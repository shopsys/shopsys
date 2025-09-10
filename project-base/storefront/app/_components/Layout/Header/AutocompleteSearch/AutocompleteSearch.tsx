'use client';

import { AutocompleteSearchPopup } from './AutocompleteSearchPopup';
import { AutocompleteSkeleton } from './AutocompleteSkeleton';
import { MINIMAL_SEARCH_QUERY_LENGTH } from './constants';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { SearchInput } from 'components/Forms/TextInput/SearchInput';
import { useTranslation } from 'components/providers/TranslationProvider';
import { AnimatePresence } from 'framer-motion';
import { useRouter } from 'next/navigation';
import { ReactNode, Suspense, use, useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useDebounce } from 'utils/useDebounce';

type SearchInputProps = {
    search: (keyword: string) => Promise<ReactNode>;
};

export const AutocompleteSearch = ({ search }: SearchInputProps) => {
    const { t } = useTranslation();
    const router = useRouter();

    const [keyword, setKeyword] = useState<string>('');
    const [searchPromise, setSearchPromise] = useState<Promise<ReactNode> | null>(null);

    const debouncedSearchQuery = useDebounce(keyword, 300);
    const isWithValidSearchQuery = debouncedSearchQuery.length >= MINIMAL_SEARCH_QUERY_LENGTH;

    useEffect(() => {
        if (debouncedSearchQuery) {
            if (debouncedSearchQuery.length < MINIMAL_SEARCH_QUERY_LENGTH) {
                return;
            }

            setSearchPromise(search(keyword));
        } else {
            setSearchPromise(null);
        }
    }, [debouncedSearchQuery, search]);

    const handleSearch = () => {
        if (isWithValidSearchQuery) {
            router.push(`/search?q=${encodeURIComponent(debouncedSearchQuery)}`);
        }
    };

    return (
        <>
            <div className={twJoin('relative flex w-full transition-all', isWithValidSearchQuery && 'z-aboveOverlay')}>
                <SearchInput
                    ariaLabelForSearchButton={t('Search')}
                    className="w-full"
                    label={t('Write what you are looking for...')}
                    shouldShowSpinnerInInput={false}
                    value={keyword}
                    onChange={(e) => setKeyword(e.currentTarget.value)}
                    onClear={() => setKeyword('')}
                    onSearch={handleSearch}
                />

                <AnimatePresence>
                    {isWithValidSearchQuery && searchPromise && (
                        <AutocompleteSearchPopup handleClosePopup={() => setKeyword('')}>
                            <Suspense fallback={<AutocompleteSkeleton />}>
                                <SearchResults searchPromise={searchPromise} />
                            </Suspense>
                        </AutocompleteSearchPopup>
                    )}
                </AnimatePresence>
            </div>

            <Overlay isActive={isWithValidSearchQuery} onClick={() => setKeyword('')} />
        </>
    );
};

const SearchResults = ({ searchPromise }: { searchPromise: Promise<ReactNode> }) => {
    return <>{use(searchPromise)}</>;
};
