import { AnimateAppearSlideY } from 'components/Basic/Animations/AnimateAppearSlideY';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { AnimateSlideDiv } from 'components/Basic/Animations/AnimateSlideDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Tag } from 'components/Basic/Tag/Tag';
import { twJoin } from 'tailwind-merge';
import { createAriaParameter } from 'utils/accessibility/createAriaParameter';

export const FilterGroupWrapper: FC = ({ children }) => <div className="vl:py-5 py-4">{children}</div>;

export const FilterGroupTitle: FC<{
    isOpen: boolean;
    title: string;
    onClick: () => void;
    isActive: boolean;
    ariaLabel: string;
}> = ({ isOpen, title, onClick, isActive, ariaLabel }) => {
    return (
        <button
            aria-controls={createAriaParameter('filter-group', title)}
            aria-expanded={isOpen}
            aria-label={ariaLabel}
            className="font-secondary text-text-default flex w-full cursor-pointer items-center justify-between rounded-sm font-semibold uppercase"
            tabIndex={0}
            type="button"
            onClick={onClick}
        >
            <span className="h6 flex items-center gap-2.5 text-left">
                {title}
                {isActive && <span className="bg-background-success vl:hidden size-2 rounded-full" />}
            </span>
            <ArrowIcon className={twJoin('size-5 rotate-0 text-xs transition select-none', isOpen && 'rotate-180')} />
        </button>
    );
};

export const FilterGroupContent: FC<{ keyName?: string; id?: string }> = ({ children, keyName, id }) => (
    <AnimateCollapseDiv className="!block" keyName={keyName}>
        <div className="vl:pb-0 vl:pt-2.5 !flex flex-col flex-wrap gap-2.5 pt-4 pb-1" id={id}>
            {children}
        </div>
    </AnimateCollapseDiv>
);

export const FilterGroupContentItem: FC<{ isDisabled: boolean; keyName?: string }> = ({
    children,
    isDisabled,
    keyName,
}) => (
    <AnimateAppearSlideY className={twJoin('!block', isDisabled && 'opacity-30')} keyName={keyName}>
        {children}
    </AnimateAppearSlideY>
);

export const ShowAllButton: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <button
        tabIndex={0}
        className={twJoin(
            'w-fit cursor-pointer rounded-sm border-none bg-none p-0 text-sm underline outline-hidden hover:bg-none hover:no-underline',
            'text-link-default',
            'hover:text-link-hovered',
        )}
        onClick={onClick}
    >
        {children}
    </button>
);

export const SelectedParametersName: FC = ({ children }) => (
    <p className="font-secondary text-input-placeholder-default text-xs font-semibold">{children}</p>
);

export const SelectedParametersList: FC<{ keyName?: string }> = ({ children, keyName }) => (
    <AnimateSlideDiv className="!flex flex-wrap items-center gap-x-2.5 gap-y-2" direction="right" keyName={keyName}>
        {children}
    </AnimateSlideDiv>
);

export const SelectedParametersListItem: FC<{ onClick: () => void; ariaLabel: string }> = ({
    children,
    onClick,
    ariaLabel,
}) => (
    <Tag
        ariaLabel={ariaLabel}
        className="bg-background-accent-less text-text-default group last-of-type:mr-6"
        onClick={onClick}
    >
        {children}
    </Tag>
);
