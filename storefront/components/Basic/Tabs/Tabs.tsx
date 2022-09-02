import {
    TabsContentInStyled,
    TabsContentMobileHeadingStyled,
    TabsContentStyled,
    TabsIconStyled,
    TabsListItemStyled,
    TabsListStyled,
    TabsStyled,
} from './Tabs.style';
import React, { FC, useState } from 'react';

/**
 * In background of styled tab parts we are using - react-tabs components
 * https://github.com/reactjs/react-tabs
 */
type TabsProps = {
    headingTextMobile: string;
};

type TabFC<T = unknown> = FC<T> & { tabsRole: string };

const TabsExtended: TabFC = (props) => {
    return <TabsStyled {...props}>{props.children}</TabsStyled>;
};

const TabsListExtended: TabFC = (props) => {
    return <TabsListStyled {...props}>{props.children}</TabsListStyled>;
};

const TabsListItemExtended: TabFC = (props) => {
    return (
        <TabsListItemStyled selectedClassName="active" {...props}>
            {props.children}
        </TabsListItemStyled>
    );
};

const TabsContentExtended: TabFC<TabsProps> = (props) => {
    const [isActiveOnMobile, setIsActiveOnMobile] = useState<boolean | undefined>(false);
    const mobileTab = () => {
        setIsActiveOnMobile(!isActiveOnMobile);
    };

    return (
        <TabsContentStyled forceRender={true} selectedClassName="active" {...props}>
            <TabsContentMobileHeadingStyled onClick={mobileTab}>
                {props.headingTextMobile}
                <TabsIconStyled iconType="icon" icon="Arrow" isActive={isActiveOnMobile} />
            </TabsContentMobileHeadingStyled>
            <TabsContentInStyled {...props} isActiveOnMobile={isActiveOnMobile}>
                {props.children}
            </TabsContentInStyled>
        </TabsContentStyled>
    );
};

// define element roles needed for react-tabs component
TabsListItemExtended.tabsRole = 'Tab';
TabsListExtended.tabsRole = 'TabList';
TabsExtended.tabsRole = 'Tabs';
TabsContentExtended.tabsRole = 'TabPanel';

export {
    TabsExtended as Tabs,
    TabsListExtended as TabsList,
    TabsListItemExtended as TabsListItem,
    TabsContentExtended as TabsContent,
};
