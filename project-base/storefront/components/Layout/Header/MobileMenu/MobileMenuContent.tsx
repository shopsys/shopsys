import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
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
            <div className="flex items-center p-5">
                {!!historyMenuGroups?.length && (
                    <button
                        className="flex cursor-pointer items-center justify-center text-icon-less hover:text-icon-default"
                        tabIndex={0}
                        title={t('Back')}
                        onClick={() => handleBackClick(historyMenuGroups)}
                    >
                        <ArrowIcon className="size-6 rotate-90" />
                    </button>
                )}

                <span className="h-6 flex-1 text-center font-secondary font-semibold">
                    {currentGroupTitle ? currentGroupTitle : t('Menu')}
                </span>

                <button
                    className="flex cursor-pointer items-center justify-center text-icon-less hover:text-icon-default"
                    tabIndex={0}
                    title={t('Close')}
                    onClick={onMenuToggleHandler}
                >
                    <RemoveIcon className="size-6" />
                </button>
            </div>

            <MenuItems
                id="animation-visible-element"
                isHidden={false}
                menuItems={currentMenuItems}
                onExpand={handleExpandClick}
                onNavigate={onMenuToggleHandler}
            />

            <MenuItems
                className="absolute top-16"
                id="animation-hidden-element"
                isHidden
                menuItems={currentMenuItems}
                onExpand={handleExpandClick}
                onNavigate={onMenuToggleHandler}
            />

            <SubMenu onNavigate={onMenuToggleHandler} />
        </div>
    );
};

const MenuItems: FC<{
    id: string;
    isHidden: boolean;
    menuItems: MenuItem[];
    onExpand: (item: MenuItem) => void;
    onNavigate: () => void;
}> = ({ className, id, isHidden, menuItems, onExpand, onNavigate }) => {
    return (
        <div aria-hidden={isHidden} className={twJoin('w-90 divide-y divide-border-less px-5', className)} id={id}>
            {menuItems.map((navigationItem) => (
                <DropdownMenuListItem
                    key={(navigationItem.link ?? navigationItem.name) + navigationItem.name + id}
                    isHidden={isHidden}
                    navigationItem={navigationItem}
                    onExpand={() => onExpand(navigationItem)}
                    onNavigate={onNavigate}
                />
            ))}
        </div>
    );
};
