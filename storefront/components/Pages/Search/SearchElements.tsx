import { Webline } from 'components/Layout/Webline/Webline';
import { FC } from 'react';
import { twJoin } from 'tailwind-merge';

export const SearchResults: FC = ({ children }) => (
    <div className="relative mb-8 flex flex-col vl:mb-10 vl:flex-row vl:flex-wrap">{children}</div>
);

export const SearchResultsPanel: FC<{ isOpen: boolean }> = ({ children, isOpen }) => (
    <div
        className={twJoin(
            'fixed top-0 left-0 bottom-0 right-10 max-w-md -translate-x-full vl:static vl:w-80 vl:translate-x-0 vl:transition-none',
            isOpen && 'z-aboveOverlay translate-x-0 transition',
        )}
    >
        {children}
    </div>
);

export const SearchResultsContent: FC = ({ children }) => <div className="flex flex-1 flex-col">{children}</div>;

export const SearchResultsBlock: FC<{ areAllResultsVisible: boolean }> = ({ children, areAllResultsVisible }) => (
    <div className={twJoin('lg:overflow-hidden', !areAllResultsVisible && 'lg:max-h-40')}>{children}</div>
);

export const SearchResultsWebline: FC = ({ children }) => <Webline className="mt-6">{children}</Webline>;

export const ShowResultsButtonWrapper: FC = ({ children }) => (
    <div className="my-5 hidden justify-center lg:flex">{children}</div>
);

export const SearchResultsPanelOpener: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <div
        className="relative mb-3 flex h-12 w-full cursor-pointer flex-row justify-center rounded-xl bg-primary py-3 px-8 font-bold uppercase leading-7 text-white sm:w-44 vl:hidden"
        onClick={onClick}
    >
        {children}
    </div>
);
