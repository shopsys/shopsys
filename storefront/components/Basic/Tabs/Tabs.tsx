// eslint-disable-next-line no-use-before-define
import React, { FC, useState } from 'react';
import {
    TabsContentInStyled,
    TabsContentMobileHeadingStyled,
    TabsContentStyled,
    TabsIconStyled,
    TabsListItemStyled,
    TabsListStyled,
    TabsStyled,
} from './Tabs.style';

/**
 * TabsExtended component wrapping tabs and its content
 * In background of styled tab parts we are using - react-tabs components
 * https://github.com/reactjs/react-tabs
 */
type TabsProps = {
    /**
     * Text used as mobile tab variant
     */
    headingTextMobile: string;
};

type TabFunctionComponentType = FC & { tabsRole: string };
type TabFunctionComponentContentType = FC<TabsProps> & { tabsRole: string };

/*
 * TabsExtended element is wrap around all tabs and its content
 */
const TabsExtended: TabFunctionComponentType = (props) => {
    return <TabsStyled {...props}>{props.children}</TabsStyled>;
};

/*
 * TabsListExtended element is wrap around all tabs
 */
const TabsListExtended: TabFunctionComponentType = (props) => {
    return <TabsListStyled {...props}>{props.children}</TabsListStyled>;
};

/*
 * TabsListItemExtended element creates link with tab functionality - shows contant according to ShopsysTabsContent index order
 */
const TabsListItemExtended: TabFunctionComponentType = (props) => {
    return (
        <TabsListItemStyled selectedClassName="active" {...props}>
            {props.children}
        </TabsListItemStyled>
    );
};

/*
 * TabsContentExtended element is wrap around tab content
 * On mobile devices there is diplayed only TabsContentMobileHeading (special element with closing arrow icon)
 * and shows content on click event and you have to define headingTextMobile - you can use shorter text
 */
const TabsContentExtended: TabFunctionComponentContentType = (props) => {
    const [isActiveOnMobile, setIsActiveOnMobile] = useState<boolean | undefined>(false);
    const mobileTab = () => {
        setIsActiveOnMobile(!isActiveOnMobile);
    };

    return (
        <TabsContentStyled forceRender={true} selectedClassName="active" {...props}>
            <TabsContentMobileHeadingStyled onClick={mobileTab}>
                {props.headingTextMobile}
                <TabsIconStyled icon="Arrow" isActive={isActiveOnMobile} />
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

/* @component */
export {
    TabsExtended as Tabs,
    TabsListExtended as TabsList,
    TabsListItemExtended as TabsListItem,
    TabsContentExtended as TabsContent,
};
