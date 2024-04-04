import { DropdownMenuListItem } from './MobileMenuListItem';
import { SubMenu } from './MobileMenuSubItems';
import { mapNavigationMenuItems } from './utils';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { NavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

export type MenuItem = {
    name: string;
    link: string;
    parentItem?: string;
    children?: MenuItem[];
};

type MobileMenuContentProps = {
    navigationItems: NavigationQuery['navigation'];
    onMenuToggleHandler: () => void;
};

export const MobileMenuContent: FC<MobileMenuContentProps> = ({ navigationItems, onMenuToggleHandler }) => {
    const { t } = useTranslation();
    const [historyMenuGroups, setHistoryMenuGroups] = useState<MenuItem[][] | undefined>();
    const [currentMenuItems, setCurrentMenuItems] = useState<MenuItem[]>(mapNavigationMenuItems(navigationItems));

    const currentGroupTitle = currentMenuItems[0].parentItem;

    const handleBackClick = (historyMenuGroups: MenuItem[][]) => {
        const previousHistoryMenuGroups = [...historyMenuGroups].pop()!;

        setCurrentMenuItems(previousHistoryMenuGroups);
        setHistoryMenuGroups(historyMenuGroups.slice(0, -1));
    };

    return (
        <>
            <div className="py-3 flex">
                {!!historyMenuGroups?.length && (
                    <button
                        className="cursor-pointer flex w-9 justify-start gap-2 items-center text-sm uppercase text-dark"
                        title={t('Back')}
                        onClick={() => handleBackClick(historyMenuGroups)}
                    >
                        <ArrowIcon className="rotate-90 w-5" />
                    </button>
                )}

                {currentGroupTitle && (
                    <span className="text-center flex-1 leading-5 uppercase">{currentGroupTitle}</span>
                )}

                <button
                    className="cursor-pointer ml-auto flex w-9 justify-end gap-2 items-center text-sm uppercase text-dark"
                    title={t('Close')}
                    onClick={onMenuToggleHandler}
                >
                    <CloseIcon className="w-5" />
                </button>
            </div>

            <div>
                {currentMenuItems.map((navigationItem) => (
                    <DropdownMenuListItem
                        key={navigationItem.link}
                        navigationItem={navigationItem}
                        onNavigate={onMenuToggleHandler}
                        onExpand={() => {
                            setHistoryMenuGroups([...(historyMenuGroups || []), currentMenuItems]);
                            setCurrentMenuItems(navigationItem.children!);
                        }}
                    />
                ))}
            </div>

            <SubMenu onNavigate={onMenuToggleHandler} />
        </>
    );
};
