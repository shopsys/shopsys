import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import type { KeyboardEventHandler, Ref } from 'react';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { getNavigationItemSkeletonType } from './navigationUtils';

type NavigationItemProps = {
    navigationItem: TypeCategoriesByColumnFragment;
    isMenuOpened: boolean;
    shouldReduceMotion: boolean;
    handleAnimations: () => void;
    itemRef?: Ref<HTMLLIElement>;
    itemClassName?: string;
    onMenuClose: () => void;
    onMenuOpen: () => void;
};

export const NavigationItem: FC<NavigationItemProps> = ({
    navigationItem,
    isMenuOpened,
    shouldReduceMotion,
    handleAnimations,
    itemRef,
    itemClassName,
    onMenuClose,
    onMenuOpen,
}) => {
    const hasChildren = !!navigationItem.categoriesByColumns.length;
    const { url } = useDomainConfig();
    const [catalogUrl] = getInternationalizedStaticUrls(['/catalog'], url);
    const skeletonType = getNavigationItemSkeletonType(navigationItem, catalogUrl);

    const handleKeyDown: KeyboardEventHandler<HTMLLIElement> = (event) => {
        if (event.key === 'Escape') {
            onMenuClose();
        }
    };

    return (
        /* biome-ignore lint/a11y/noNoninteractiveElementInteractions: Hover state belongs on the list item so the menu keeps its navigation list semantics. */
        <li
            className="group"
            ref={itemRef}
            onFocus={() => {
                if (!hasChildren) {
                    onMenuClose();

                    return;
                }

                onMenuOpen();
            }}
            onKeyDown={handleKeyDown}
            onMouseEnter={() => {
                if (!hasChildren) {
                    onMenuClose();

                    return;
                }

                onMenuOpen();

                if (!shouldReduceMotion) {
                    handleAnimations();
                }
            }}
        >
            <ExtendedNextLink
                href={navigationItem.link}
                skeletonType={skeletonType}
                className={twMergeCustom(
                    'relative m-0 flex items-center whitespace-nowrap p-5 font-secondary font-semibold text-sm vl:text-base group-first-of-type:pl-0 group-last-of-type:pr-0',
                    'text-link-inverted-default no-underline',
                    'hover:text-link-inverted-hovered hover:no-underline group-hover:text-link-inverted-hovered group-hover:no-underline',
                    'active:text-link-inverted-hovered',
                    'disabled:text-link-inverted-disabled',
                    itemClassName,
                )}
            >
                {navigationItem.name}
                {hasChildren && (
                    <div
                        className={twJoin(
                            'ml-1 flex items-start motion-safe:transition-transform motion-safe:duration-200',
                            isMenuOpened && 'rotate-180',
                        )}
                    >
                        <ArrowIcon
                            className={twJoin(
                                'size-5 text-link-inverted-default transition',
                                isMenuOpened && 'group-hover:text-link-inverted-hovered',
                            )}
                        />
                    </div>
                )}
            </ExtendedNextLink>
        </li>
    );
};
