import { AnimateAppearSlideY } from 'components/Basic/Animations/AnimateAppearSlideY';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { AnimateSlideDiv } from 'components/Basic/Animations/AnimateSlideDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { LabelLink } from 'components/Basic/LabelLink/LabelLink';
import { twJoin } from 'tailwind-merge';

export const FilterGroupWrapper: FC = ({ children }) => <div className="vl:py-5 py-4">{children}</div>;

export const FilterGroupTitle: FC<{ isOpen: boolean; title: string; onClick: () => void; isActive: boolean }> = ({
    isOpen,
    title,
    onClick,
    isActive,
}) => (
    <div
        className="font-secondary text-text-default flex cursor-pointer items-center justify-between font-semibold uppercase"
        onClick={onClick}
    >
        <h6 className="flex items-center gap-2.5">
            {title}
            {isActive && <div className="bg-text-success vl:hidden size-2 rounded-full" />}
        </h6>
        <ArrowIcon className={twJoin('size-5 rotate-0 text-xs transition select-none', isOpen && 'rotate-180')} />
    </div>
);

export const FilterGroupContent: FC<{ keyName?: string }> = ({ children, keyName }) => (
    <AnimateCollapseDiv className="!block" keyName={keyName}>
        <div className="vl:pb-0 vl:pt-2.5 !flex flex-col flex-wrap gap-2.5 pt-4 pb-1">{children}</div>
    </AnimateCollapseDiv>
);

export const FilterGroupContentItem: FC<{ isDisabled: boolean; keyName?: string }> = ({
    children,
    isDisabled,
    keyName,
}) => (
    <AnimateAppearSlideY className={twJoin('!block', isDisabled && 'pointer-events-none opacity-30')} keyName={keyName}>
        {children}
    </AnimateAppearSlideY>
);

export const ShowAllButton: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <button
        className={twJoin(
            'w-fit cursor-pointer border-none bg-none p-0 text-sm underline outline-hidden hover:bg-none hover:no-underline',
            'text-link',
            'hover:text-linkHovered',
        )}
        onClick={onClick}
    >
        {children}
    </button>
);

export const SelectedParametersName: FC = ({ children }) => (
    <p className="font-secondary text-inputPlaceholder text-xs font-semibold">{children}</p>
);

export const SelectedParametersList: FC<{ keyName?: string }> = ({ children, keyName }) => (
    <AnimateSlideDiv className="!flex flex-wrap items-center gap-x-2.5 gap-y-2" direction="right" keyName={keyName}>
        {children}
    </AnimateSlideDiv>
);

export const SelectedParametersListItem: FC<{ onClick: () => void }> = ({ children, onClick }) => (
    <LabelLink className="bg-backgroundAccentLess text-text-default group last-of-type:mr-6" onClick={onClick}>
        {children}
    </LabelLink>
);
