import { DropdownMenuListItem } from './MobileMenuListItem';
import { SubMenu } from './MobileMenuSubItems';
import { mapNavigationMenuItems } from './mobileMenuUtils';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { AnimationSequence, useAnimate } from 'framer-motion';
import { TypeNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
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

    const currentGroupTitle = currentMenuItems[0].parentItem;

    const handleExpandClick = (navigationItem: MenuItem) => {
        const slideAwayThenTeleportIntoViewSequence: AnimationSequence = [
            ['#animation-visible-element', { transform: 'translateX(-120%)' }, { duration: 0.2, type: 'tween' }],
            ['#animation-visible-element', { transform: 'translateX(0)' }, { duration: 0 }],
        ];

        const slideIntoViewThenTeleportAwaySequence: AnimationSequence = [
            ['#animation-hidden-element', { transform: 'translateX(0)' }, { duration: 0.2, type: 'tween' }],
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
            ['#animation-visible-element', { transform: 'translateX(0)' }, { duration: 0.2, type: 'tween' }],
        ];
        const teleportIntoViewThenSlideAwaySequence: AnimationSequence = [
            ['#animation-hidden-element', { transform: 'translateX(0)' }, { duration: 0 }],
            ['#animation-hidden-element', { transform: 'translateX(120%)' }, { duration: 0.2, type: 'tween' }],
        ];

        animate(teleportAwayThenSlideIntoViewSequence);
        animate(teleportIntoViewThenSlideAwaySequence);

        const previousHistoryMenuGroups = [...historyMenuGroups].pop()!;
        setCurrentMenuItems(previousHistoryMenuGroups);
        setHistoryMenuGroups(historyMenuGroups.slice(0, -1));
    };

    return (
        <div ref={scope}>
            <div className="mb-5 flex py-3">
                {!!historyMenuGroups?.length && (
                    <button
                        className="text-text-default flex w-9 cursor-pointer items-center justify-start gap-2 text-sm uppercase"
                        title={t('Back')}
                        onClick={() => handleBackClick(historyMenuGroups)}
                    >
                        <ArrowIcon className="size-5 rotate-90" />
                    </button>
                )}

                {currentGroupTitle && (
                    <span className="flex-1 text-center leading-5 uppercase">{currentGroupTitle}</span>
                )}

                <button
                    className="text-text-default ml-auto flex w-9 cursor-pointer items-center justify-end gap-2 text-sm uppercase"
                    title={t('Close')}
                    onClick={onMenuToggleHandler}
                >
                    <CloseIcon className="w-5" />
                </button>
            </div>

            <MenuItems
                id="animation-visible-element"
                menuItems={currentMenuItems}
                onExpand={handleExpandClick}
                onNavigate={onMenuToggleHandler}
            />

            <MenuItems
                className="translate-x-[120%]"
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
        <div className={twJoin('divide-borderAccent absolute w-[calc(100%-4rem)] divide-y', className)} id={id}>
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
