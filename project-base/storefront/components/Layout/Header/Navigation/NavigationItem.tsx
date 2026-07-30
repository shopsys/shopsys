import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { NavigationItemContent } from 'components/Layout/Header/Navigation/NavigationItemContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import type { KeyboardEventHandler, MouseEventHandler, Ref } from 'react';
import { useRef } from 'react';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { getNavigationItemSkeletonType, isNavigationItemLink, isNavigationItemWithCategories } from './navigationUtils';

type NavigationItemProps = {
    navigationItem: TypeCategoriesByColumnFragment;
    isMenuOpened: boolean;
    shouldReduceMotion: boolean;
    handleAnimations: () => void;
    itemRef?: Ref<HTMLLIElement>;
    itemClassName?: string;
    menuId: string;
    onMenuClose: () => void;
    onMenuOpen: () => void;
    onMenuOpenAndFocus: () => void;
    onMenuOpenImmediately: () => void;
};

export const NavigationItem: FC<NavigationItemProps> = ({
    navigationItem,
    isMenuOpened,
    shouldReduceMotion,
    handleAnimations,
    itemRef,
    itemClassName,
    menuId,
    onMenuClose,
    onMenuOpen,
    onMenuOpenAndFocus,
    onMenuOpenImmediately,
}) => {
    const { url } = useDomainConfig();
    const triggerRef = useRef<HTMLButtonElement>(null);
    const [catalogUrl] = getInternationalizedStaticUrls(['/catalog'], url);
    const skeletonType = getNavigationItemSkeletonType(navigationItem, catalogUrl);
    const isLink = isNavigationItemLink(navigationItem);
    const isDropdownTrigger = isNavigationItemWithCategories(navigationItem);
    const link = navigationItem.link;

    const handleKeyDown: KeyboardEventHandler<HTMLLIElement> = (event) => {
        if (event.key === 'Escape') {
            onMenuClose();
            triggerRef.current?.focus();
        }
    };

    const handleDropdownTriggerKeyDown: KeyboardEventHandler<HTMLButtonElement> = (event) => {
        if (event.key !== 'ArrowDown') {
            return;
        }

        event.preventDefault();
        onMenuOpenAndFocus();
    };

    const handleMouseEnter: MouseEventHandler<HTMLLIElement> = () => {
        if (!isDropdownTrigger) {
            onMenuClose();

            return;
        }

        onMenuOpen();

        if (!shouldReduceMotion) {
            handleAnimations();
        }
    };

    const isExternalLink = link !== null && (link.startsWith('http://') || link.startsWith('https://'));
    const navigationItemClassName = twMergeCustom(
        'relative m-0 flex cursor-pointer items-center whitespace-nowrap rounded-sm border-0 bg-transparent p-5 font-secondary font-semibold text-sm vl:text-base group-first-of-type:pl-0 group-last-of-type:pr-0',
        'text-link-inverted-default no-underline',
        'hover:text-link-inverted-hovered hover:no-underline group-hover:text-link-inverted-hovered group-hover:no-underline',
        'active:text-link-inverted-hovered',
        'disabled:text-link-inverted-disabled',
        itemClassName,
    );

    return (
        /* biome-ignore lint/a11y/noNoninteractiveElementInteractions: Hover state belongs on the list item so the menu keeps its navigation list semantics. */
        <li
            className="group"
            ref={itemRef}
            onFocus={() => {
                if (isDropdownTrigger) {
                    onMenuOpenImmediately();
                } else {
                    onMenuClose();
                }
            }}
            onKeyDown={handleKeyDown}
            onMouseEnter={handleMouseEnter}
        >
            {isLink && link !== null ? (
                <ExtendedNextLink
                    href={link}
                    rel={isExternalLink ? 'nofollow noreferrer noopener' : undefined}
                    skeletonType={skeletonType}
                    target={isExternalLink ? '_blank' : undefined}
                    className={navigationItemClassName}
                >
                    <NavigationItemContent isDropdownTrigger={false} isMenuOpened={false} name={navigationItem.name} />
                </ExtendedNextLink>
            ) : (
                <button
                    ref={isDropdownTrigger ? triggerRef : undefined}
                    aria-controls={isDropdownTrigger ? menuId : undefined}
                    aria-expanded={isDropdownTrigger ? isMenuOpened : undefined}
                    aria-haspopup={isDropdownTrigger ? 'true' : undefined}
                    className={navigationItemClassName}
                    type="button"
                    onClick={isMenuOpened ? onMenuClose : onMenuOpenImmediately}
                    onKeyDown={isDropdownTrigger ? handleDropdownTriggerKeyDown : undefined}
                >
                    <NavigationItemContent
                        isDropdownTrigger={isDropdownTrigger}
                        isMenuOpened={isMenuOpened}
                        name={navigationItem.name}
                    />
                </button>
            )}
        </li>
    );
};
