import { DrawerCloseButton } from 'components/Basic/Drawer/DrawerCloseButton';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { AnimationSequence, useAnimate, useReducedMotion } from 'framer-motion';
import { TypeNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { DropdownMenuListItem } from './MobileMenuListItem';
import { SubMenu } from './MobileMenuSubItems';
import { mapNavigationMenuItems } from './mobileMenuUtils';

export type MenuItem = {
    name: string;
    link: string | null;
    parentItem?: string;
    children?: MenuItem[];
    isViewAllLink?: boolean;
};

type MobileMenuContentProps = {
    navigationItems: TypeNavigationQuery['navigation'];
    onMenuToggleHandler: () => void;
};

export const MobileMenuContent: FC<MobileMenuContentProps> = ({ navigationItems, onMenuToggleHandler }) => {
    const { t } = useTranslation();
    const [historyMenuGroups, setHistoryMenuGroups] = useState<MenuItem[][] | undefined>();
    const [currentMenuItems, setCurrentMenuItems] = useState<MenuItem[]>(mapNavigationMenuItems(navigationItems));
    const [scope, animate] = useAnimate();
    const shouldReduceMotion = useReducedMotion();

    const currentGroupTitle = currentMenuItems[0].parentItem;
    const menuSectionTitle = currentGroupTitle ?? t('Categories');

    useEffect(() => {
        // Set initial positions programmatically to ensure first navigation animations work correctly
        animate('#animation-hidden-element', { transform: 'translateX(120%)' }, { duration: 0 });
    }, [animate]);

    const handleExpandClick = (navigationItem: MenuItem) => {
        const slideAwayThenTeleportIntoViewSequence: AnimationSequence = [
            [
                '#animation-visible-element',
                { transform: 'translateX(-120%)' },
                { duration: shouldReduceMotion ? 0 : 0.2, type: 'tween' },
            ],
            ['#animation-visible-element', { transform: 'translateX(0)' }, { duration: 0 }],
        ];

        const slideIntoViewThenTeleportAwaySequence: AnimationSequence = [
            [
                '#animation-hidden-element',
                { transform: 'translateX(0)' },
                { duration: shouldReduceMotion ? 0 : 0.2, type: 'tween' },
            ],
            ['#animation-hidden-element', { transform: 'translateX(120%)' }, { duration: 0 }],
        ];

        animate(slideAwayThenTeleportIntoViewSequence);
        animate(slideIntoViewThenTeleportAwaySequence);

        setHistoryMenuGroups([...(historyMenuGroups || []), currentMenuItems]);
        setCurrentMenuItems([
            ...navigationItem.children!,
            ...(navigationItem.link !== null
                ? [
                      {
                          name: t('View all in {{ categoryName }}', { categoryName: navigationItem.name }),
                          link: navigationItem.link,
                          parentItem: navigationItem.name,
                          isViewAllLink: true,
                      },
                  ]
                : []),
        ]);
    };

    const handleBackClick = (historyMenuGroups: MenuItem[][]) => {
        const teleportAwayThenSlideIntoViewSequence: AnimationSequence = [
            ['#animation-visible-element', { transform: 'translateX(-120%)' }, { duration: 0 }],
            [
                '#animation-visible-element',
                { transform: 'translateX(0)' },
                { duration: shouldReduceMotion ? 0 : 0.2, type: 'tween' },
            ],
        ];
        const teleportIntoViewThenSlideAwaySequence: AnimationSequence = [
            ['#animation-hidden-element', { transform: 'translateX(0)' }, { duration: 0 }],
            [
                '#animation-hidden-element',
                { transform: 'translateX(120%)' },
                { duration: shouldReduceMotion ? 0 : 0.2, type: 'tween' },
            ],
        ];

        animate(teleportAwayThenSlideIntoViewSequence);
        animate(teleportIntoViewThenSlideAwaySequence);

        const previousHistoryMenuGroups = [...historyMenuGroups].pop()!;
        setCurrentMenuItems(previousHistoryMenuGroups);
        setHistoryMenuGroups(historyMenuGroups.slice(0, -1));
    };

    return (
        <div className="flex h-full flex-col" ref={scope}>
            <div className="grid grid-cols-[2.25rem_1fr_2.25rem] items-center gap-3 p-5">
                {historyMenuGroups?.length ? (
                    <IconButton
                        Icon={ArrowIcon}
                        iconClassName="rotate-90"
                        title={t('Back')}
                        onClick={() => handleBackClick(historyMenuGroups)}
                    />
                ) : (
                    <span aria-hidden="true" />
                )}

                <span className="min-w-0 truncate text-center font-secondary font-semibold">{t('Menu')}</span>

                <DrawerCloseButton onClick={onMenuToggleHandler} />
            </div>

            <div className="relative">
                <MenuItems
                    id="animation-visible-element"
                    isHidden={false}
                    menuItems={currentMenuItems}
                    title={menuSectionTitle}
                    onExpand={handleExpandClick}
                    onNavigate={onMenuToggleHandler}
                />

                <MenuItems
                    className="absolute inset-x-0 top-0"
                    id="animation-hidden-element"
                    isHidden
                    menuItems={currentMenuItems}
                    title={menuSectionTitle}
                    onExpand={handleExpandClick}
                    onNavigate={onMenuToggleHandler}
                />
            </div>

            <SubMenu onNavigate={onMenuToggleHandler} />
        </div>
    );
};

const MenuItems: FC<{
    id: string;
    isHidden: boolean;
    menuItems: MenuItem[];
    title?: string;
    onExpand: (item: MenuItem) => void;
    onNavigate: () => void;
}> = ({ className, id, isHidden, menuItems, title, onExpand, onNavigate }) => {
    const { t } = useTranslation();
    const viewAllItem = menuItems.find((menuItem) => menuItem.isViewAllLink && menuItem.link !== null);
    const categoryItems = menuItems.filter((menuItem) => !menuItem.isViewAllLink);

    return (
        <div aria-hidden={isHidden} className={twJoin('w-full px-5', className)} id={id}>
            <div className="mb-2 flex items-center justify-between gap-3">
                <p
                    aria-hidden={!title}
                    className={twJoin(
                        'min-w-0 truncate font-secondary font-semibold text-text-less text-xs uppercase',
                        !title && 'invisible',
                    )}
                >
                    {title}
                </p>

                {viewAllItem?.link && (
                    <ExtendedNextLink
                        aria-label={viewAllItem.name}
                        className="inline-flex shrink-0 items-center gap-1 rounded-md py-1 font-secondary font-semibold text-link-default text-xs no-underline hover:underline"
                        href={viewAllItem.link}
                        skeletonType={DEFAULT_SKELETON_TYPE}
                        tabIndex={isHidden ? -1 : undefined}
                        onClick={onNavigate}
                    >
                        <span>{t('View all')}</span>
                        <ArrowIcon className="size-4 -rotate-90" />
                    </ExtendedNextLink>
                )}
            </div>

            <div className="flex flex-col gap-2">
                {categoryItems.map((navigationItem) => (
                    <DropdownMenuListItem
                        key={(navigationItem.link ?? navigationItem.name) + navigationItem.name + id}
                        isHidden={isHidden}
                        navigationItem={navigationItem}
                        onExpand={() => onExpand(navigationItem)}
                        onNavigate={onNavigate}
                    />
                ))}
            </div>
        </div>
    );
};
