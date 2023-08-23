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
import { Arrow } from '../Icon/IconsSvg';

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
    <TabsReact className={twJoin('mb-5 xl:my-auto xl:mb-5 xl:max-w-7xl', className)} {...props}>
        {children}
    </TabsReact>
);

export const TabsList: TabFC<Partial<TabListProps>> = ({ children }) => (
    <TabList className="z-above hidden flex-row border-b border-border px-3 lg:flex">{children}</TabList>
);

export const TabsListItem: TabFC<Partial<PropsWithRef<TabProps>>> = ({ children, className, dataTestId, ...props }) => (
    <Tab
        selectedClassName="isActive"
        className={twJoin(
            'relative bottom-0 mx-4 cursor-pointer px-2 py-1 text-black no-underline before:absolute before:left-0 before:right-0 before:hidden before:bg-primary before:content-[""] hover:no-underline [&.isActive]:text-primary [&.isActive]:before:block',
            className,
        )}
        data-testid={dataTestId}
        {...props}
    >
        {children}
    </Tab>
);

export const TabsContent: TabFC<TabsContentProps & Partial<PropsWithRef<TabPanelProps>>> = ({
    children,
    headingTextMobile,
    dataTestId,
    ...props
}) => {
    const [isActiveOnMobile, setIsActiveOnMobile] = useState<boolean | undefined>(false);
    const mobileTab = () => setIsActiveOnMobile(!isActiveOnMobile);

    return (
        <TabPanel
            className="flex flex-col flex-wrap lg:hidden [&.isActive]:flex [&.isActive]:lg:pt-12"
            forceRender
            selectedClassName="isActive"
            data-testid={dataTestId}
            {...props}
        >
            <h3
                className="mb-4 flex w-full cursor-pointer items-center justify-between rounded bg-blueLight py-4 px-5 font-bold lg:hidden"
                onClick={mobileTab}
            >
                {headingTextMobile}
                <Arrow className={twJoin('w-4 rotate-0 transition', isActiveOnMobile && '-rotate-180 ')} />
            </h3>
            <div className={twJoin('py-5 ', isActiveOnMobile ? 'block' : 'hidden lg:block')}>{children}</div>
        </TabPanel>
    );
};

// define element roles needed for react-tabs component
Tabs.tabsRole = 'Tabs';
TabsList.tabsRole = 'TabList';
TabsListItem.tabsRole = 'Tab';
TabsContent.tabsRole = 'TabPanel';
