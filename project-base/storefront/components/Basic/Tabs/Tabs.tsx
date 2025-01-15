import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { AnimateRotateDiv } from 'components/Basic/Animations/AnimateRotateDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { AnimatePresence } from 'framer-motion';
import { useState } from 'react';
import {
    Tab,
    TabList,
    TabListProps,
    TabPanel,
    TabPanelProps,
    TabProps,
    TabsProps,
    Tabs as TabsReact,
} from 'react-tabs';
import { twJoin } from 'tailwind-merge';
import { useMediaMin } from 'utils/ui/useMediaMin';

/**
 * In background of styled tab parts we are using - react-tabs components
 * https://github.com/reactjs/react-tabs
 */
type TabsContentProps = {
    headingTextMobile: string;
    isActive: boolean;
    skipInitialAnimation?: boolean;
};

type TabFC<T = unknown> = FC<T> & { tabsRole: string };

// this is hack for react-tabs bug,
// when passing ...props to lib component, react-tabs are complaining about ref type
type PropsWithRef<T> = T & { ref: any };

export const Tabs: TabFC<Partial<TabsProps>> = ({ children, className, ...props }) => (
    <TabsReact className={twJoin('xl:my-auto xl:max-w-7xl', className)} {...props}>
        {children}
    </TabsReact>
);

export const TabsList: TabFC<Partial<TabListProps>> = ({ children }) => (
    <TabList className="z-above hidden flex-row lg:flex lg:gap-5">{children}</TabList>
);

export const TabsListItem: TabFC<Partial<PropsWithRef<TabProps>>> = ({ children, className, ...props }) => (
    <Tab
        selectedClassName="isActive"
        className={twJoin(
            'cursor-pointer select-none rounded-2xl bg-backgroundMore px-3 py-2 font-secondary text-sm font-semibold outline-1 outline-borderAccentSuccess [&.isActive]:bg-textInverted [&.isActive]:outline',
            className,
        )}
        {...props}
    >
        {children}
    </Tab>
);

export const TabsContent: TabFC<TabsContentProps & Partial<PropsWithRef<TabPanelProps>>> = ({
    children,
    headingTextMobile,
    isActive,
    skipInitialAnimation = false,
    ...props
}) => {
    const [isActiveOnMobile, setIsActiveOnMobile] = useState<boolean | undefined>(false);
    const mobileTab = () => setIsActiveOnMobile(!isActiveOnMobile);
    const isLg = useMediaMin('lg');

    return (
        <TabPanel
            forceRender
            className="flex flex-col flex-wrap lg:hidden [&.isActive]:flex [&.isActive]:lg:pt-5"
            selectedClassName="isActive"
            {...props}
        >
            <div
                className="flex w-full cursor-pointer items-center justify-between rounded-xl bg-backgroundMore p-3 font-secondary text-sm font-semibold lg:hidden"
                onClick={mobileTab}
            >
                {headingTextMobile}
                <AnimateRotateDiv className="flex items-start" condition={isActiveOnMobile}>
                    <ArrowIcon className={twJoin('size-4 rotate-0 text-text transition')} />
                </AnimateRotateDiv>
            </div>

            <AnimatePresence initial={false}>
                {(isActiveOnMobile || (isActive && isLg)) && (
                    <AnimateCollapseDiv
                        className="relative mt-3 !block w-full lg:mt-0"
                        initial={skipInitialAnimation ? 'open' : 'closed'}
                        keyName={`tabs-content-${headingTextMobile}`}
                    >
                        {children}
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>
        </TabPanel>
    );
};

// define element roles needed for react-tabs component
Tabs.tabsRole = 'Tabs';
TabsList.tabsRole = 'TabList';
TabsListItem.tabsRole = 'Tab';
TabsContent.tabsRole = 'TabPanel';
