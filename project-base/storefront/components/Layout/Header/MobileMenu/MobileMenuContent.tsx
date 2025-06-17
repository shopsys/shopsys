import { DropdownMenuListItem } from './MobileMenuListItem';
import { SubMenu } from './MobileMenuSubItems';
import { mapNavigationMenuItems } from './mobileMenuUtils';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { AnimationSequence, useAnimate, useReducedMotion } from 'framer-motion';
import { TypeNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';

export type MenuItem = {
    name: string;
    link: string;
    parentItem?: string;
    children?: MenuItem[];
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
    }, []);

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
        setCurrentMenuItems(navigationItem.children!);
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
                        className="text-icon-less hover:text-icon-accent flex cursor-pointer items-center justify-center p-1"
                        tabIndex={0}
                        title={t('Back')}
                        onClick={() => handleBackClick(historyMenuGroups)}
                    >
                        <ArrowIcon className="size-4 rotate-90" />
                    </button>
                )}

                <span className="h-6 flex-1 text-center text-base">
                    {currentGroupTitle ? currentGroupTitle : t('Menu')}
                </span>

                <button
                    className="text-icon-less hover:text-icon-accent flex cursor-pointer items-center justify-center p-1"
                    tabIndex={0}
                    title={t('Close')}
                    onClick={onMenuToggleHandler}
                >
                    <RemoveIcon className="size-4" />
                </button>
            </div>

            <MenuItems
                id="animation-visible-element"
                menuItems={currentMenuItems}
                onExpand={handleExpandClick}
                onNavigate={onMenuToggleHandler}
            />

            <MenuItems
                className="absolute top-16"
                id="animation-hidden-element"
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
    menuItems: MenuItem[];
    onExpand: (item: MenuItem) => void;
    onNavigate: () => void;
}> = ({ className, id, menuItems, onExpand, onNavigate }) => {
    return (
        <div className={twJoin('divide-border-default w-[315px] divide-y px-5', className)} id={id}>
            {menuItems.map((navigationItem) => (
                <DropdownMenuListItem
                    key={navigationItem.link + navigationItem.name + id}
                    navigationItem={navigationItem}
                    onExpand={() => onExpand(navigationItem)}
                    onNavigate={onNavigate}
                />
            ))}
        </div>
    );
};
