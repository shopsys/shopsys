import { DropdownMenuListItem } from './MobileMenuListItem';
import { SubMenu } from './MobileMenuSubItems';
import { mapNavigationMenuItems } from './mobileMenuUtils';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { TypeNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

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

    const currentGroupTitle = currentMenuItems[0].parentItem;

    const handleBackClick = (historyMenuGroups: MenuItem[][]) => {
        const previousHistoryMenuGroups = [...historyMenuGroups].pop()!;

        setCurrentMenuItems(previousHistoryMenuGroups);
        setHistoryMenuGroups(historyMenuGroups.slice(0, -1));
    };

    return (
        <>
            <div className="flex py-3">
                {!!historyMenuGroups?.length && (
                    <button
                        className="flex w-9 cursor-pointer items-center justify-start gap-2 text-sm uppercase text-text"
                        title={t('Back')}
                        onClick={() => handleBackClick(historyMenuGroups)}
                    >
                        <ArrowIcon className="w-5 rotate-90" />
                    </button>
                )}

                {currentGroupTitle && (
                    <span className="flex-1 text-center uppercase leading-5">{currentGroupTitle}</span>
                )}

                <button
                    className="ml-auto flex w-9 cursor-pointer items-center justify-end gap-2 text-sm uppercase text-text"
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
