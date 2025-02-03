import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { ExitIcon } from 'components/Basic/Icon/ExitIcon';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { m } from 'framer-motion';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';
import { useLogout } from 'utils/auth/useLogout';
import { useUserMenuItems } from 'utils/user/useUserMenuItems';

const getMenuItemTwClass = (menuItemLink?: string, asPath?: string) =>
    twJoin(
        'border-b border-borderAccent last:border-none no-underline px-4 py-3 flex gap-4 text-sm items-center',
        menuItemLink && asPath?.includes(menuItemLink)
            ? 'bg-backgroundAccentLess text-textAccent hover:text-textAccent'
            : 'bg-backgroundMore text-text hover:text-textAccent',
    );

export const UserNavigation: FC = () => {
    const userMenuItems = useUserMenuItems();
    const router = useRouter();
    const { t } = useTranslation();
    const [isExpanded, setIsExpanded] = useState(false);
    const logoutHandler = useLogout();

    return (
        <div className="flex h-fit min-w-64 flex-col overflow-hidden rounded-xl xs:min-w-[300px]">
            <button
                className={twJoin(
                    'flex items-center gap-4 bg-backgroundMore px-6 py-3 no-underline last:border-none lg:hidden',
                    isExpanded && 'border-b border-borderAccent',
                )}
                onClick={() => setIsExpanded((prev) => !prev)}
            >
                {isExpanded ? t('Hide menu') : t('Show menu')}
                <ArrowIcon className={twJoin('size-4 transition', isExpanded ? 'rotate-180' : 'rotate-0')} />
            </button>

            <m.div
                key="user-navigation"
                animate={isExpanded ? 'open' : 'closed'}
                className="!flex flex-col bg-backgroundMore lg:!h-auto"
                initial={false}
                variants={collapseExpandAnimation}
            >
                {userMenuItems.map((menuItem, index) => (
                    <ExtendedNextLink
                        key={index}
                        className={getMenuItemTwClass(menuItem.link, router.asPath)}
                        href={menuItem.link}
                        type={menuItem.type}
                    >
                        {menuItem.iconComponent && <menuItem.iconComponent className="size-6" />}
                        {menuItem.text}
                        {menuItem.count !== undefined && (
                            <div
                                className={twJoin(
                                    'ml-auto flex min-h-[24px] min-w-[24px] items-center justify-center rounded-full bg-backgroundDark px-1 text-sm text-textInverted',
                                )}
                            >
                                {menuItem.count}
                            </div>
                        )}
                    </ExtendedNextLink>
                ))}
                <Button
                    tid={TIDs.customer_page_logout}
                    className={twJoin(
                        getMenuItemTwClass(),
                        'font-primary justify-start bg-transparent !px-4 text-sm font-normal leading-5 !text-text !outline-none hover:bg-transparent hover:text-textAccent hover:underline',
                    )}
                    onClick={logoutHandler}
                >
                    <ExitIcon className="size-6" />
                    {t('Logout')}
                </Button>
            </m.div>
        </div>
    );
};
