import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import React, { useState } from 'react';
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

/**
 * In background of styled tab parts we are using - react-tabs components
 * https://github.com/reactjs/react-tabs
 */
type TabsContentProps = {
    headingTextMobile: string;
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
    <TabList className="z-above hidden flex-row lg:flex lg:gap-4">{children}</TabList>
);

export const TabsListItem: TabFC<Partial<PropsWithRef<TabProps>>> = ({ children, className, ...props }) => (
    <Tab
        selectedClassName="isActive"
        className={twJoin(
            'cursor-pointer select-none rounded-2xl bg-backgroundMore px-3 py-2 text-sm font-semibold outline-1 outline-borderAccentSuccess [&.isActive]:outline',
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
    ...props
}) => {
    const [isActiveOnMobile, setIsActiveOnMobile] = useState<boolean | undefined>(false);
    const mobileTab = () => setIsActiveOnMobile(!isActiveOnMobile);

    return (
        <TabPanel
            forceRender
            className="flex flex-col flex-wrap lg:hidden [&.isActive]:flex [&.isActive]:lg:pt-12"
            selectedClassName="isActive"
            {...props}
        >
            <h3
                className="flex w-full cursor-pointer items-center justify-between rounded bg-backgroundMore px-5 py-4 font-bold lg:hidden"
                onClick={mobileTab}
            >
                {headingTextMobile}
                <ArrowIcon
                    className={twJoin('w-4 rotate-0 text-text transition', isActiveOnMobile && '-rotate-180 ')}
                />
            </h3>
            <div className={twJoin('w-full py-5', isActiveOnMobile ? 'block' : 'hidden lg:block')}>{children}</div>
        </TabPanel>
    );
};

// define element roles needed for react-tabs component
Tabs.tabsRole = 'Tabs';
TabsList.tabsRole = 'TabList';
TabsListItem.tabsRole = 'Tab';
TabsContent.tabsRole = 'TabPanel';
