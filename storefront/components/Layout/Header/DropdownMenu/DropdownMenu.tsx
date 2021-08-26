import {
    DropdownMenuListStyled,
    DropdownMenuListTitleStyled,
    DropdownMenuStyled,
    DropdownMenuWrapperStyled,
} from './DropdownMenu.style';
import { FC, Fragment, useState } from 'react';
import { CSSTransition } from 'react-transition-group';
import DropdownItem from './Item';
import DropdownSlideTo from './SlideTo';
import { getNavigationItems } from '../../../../connectors/navigation/Navigation';
import SubMenu from './SubMenu';
import { useTranslation } from 'react-i18next';

type DropdownMenuProps = {
    isMenuOpened: boolean;
};

const DropdownMenu: FC<DropdownMenuProps> = (props) => {
    const { t } = useTranslation();
    const navigationItems = getNavigationItems();
    const [activeMenu, setActiveMenu] = useState<'primary' | 'secondary' | 'tertiary'>('primary');
    const [indexes, setIndexes] = useState<number[] | string[] | []>([]);
    const [slideTo, setSlideTo] = useState<'right' | 'left'>('right');
    const [menuHeight, setMenuHeight] = useState<number>();

    if (navigationItems === undefined || (Array.isArray(navigationItems) && navigationItems.length === 0)) {
        return null;
    }

    const calcHeight = (el: HTMLElement) => {
        const height = el.offsetHeight;
        setMenuHeight(height);
    };

    const changeState = (props: any) => {
        if (props.goToMenu !== undefined) {
            setActiveMenu(props.goToMenu);
        }

        if (props.slideTo !== undefined) {
            setSlideTo(props.slideTo);
        }

        if (props.index !== undefined) {
            setIndexes((oldArray: number[] | string[]) => [...oldArray, props.index]);
        }

        if (props.slideTo === 'left') {
            indexes.pop();

            if (indexes.length === 0) {
                setIndexes([]);
            } else {
                setIndexes([...indexes]);
            }
        }
    };

    return (
        <DropdownMenuWrapperStyled>
            <CSSTransition
                in={props.isMenuOpened}
                timeout={500}
                classNames="dropdown"
                onEnter={calcHeight}
                unmountOnExit
            >
                <DropdownMenuStyled slideTo={slideTo} style={{ height: menuHeight }}>
                    <CSSTransition
                        in={activeMenu === 'primary'}
                        timeout={500}
                        classNames="menu-primary"
                        unmountOnExit
                        onEnter={calcHeight}
                    >
                        <DropdownMenuListStyled>
                            {navigationItems.map((navigationItem, index) => (
                                <DropdownItem
                                    key={index}
                                    navigationItem={navigationItem}
                                    changeState={changeState}
                                    index={index}
                                    level="primary"
                                    goToMenu="secondary"
                                    slideTo="right"
                                />
                            ))}
                            <SubMenu />
                        </DropdownMenuListStyled>
                    </CSSTransition>

                    <CSSTransition
                        in={activeMenu === 'secondary'}
                        timeout={500}
                        classNames="menu-secondary"
                        unmountOnExit
                        onEnter={calcHeight}
                    >
                        <DropdownMenuListStyled>
                            <DropdownSlideTo
                                changeState={changeState}
                                goToMenu="primary"
                                slideTo="left"
                                type="stepBack"
                                iconText={t('Back')}
                            />
                            {navigationItems
                                .map((navigationItem, index) => (
                                    <Fragment key={index}>
                                        <DropdownMenuListTitleStyled>{navigationItem.name}</DropdownMenuListTitleStyled>
                                        {navigationItem.categoriesByColumns.map((columnCategories, columnIndex) => (
                                            <Fragment key={columnIndex}>
                                                {columnCategories.categories.map(
                                                    (columnCategory, columnCategoryIndex) => (
                                                        <DropdownItem
                                                            key={columnCategoryIndex}
                                                            columnCategory={columnCategory}
                                                            changeState={changeState}
                                                            level="secondary"
                                                            goToMenu="tertiary"
                                                            index={
                                                                columnCategories.columnNumber +
                                                                '-' +
                                                                columnCategoryIndex
                                                            }
                                                            slideTo="right"
                                                            variant="small"
                                                        />
                                                    ),
                                                )}
                                            </Fragment>
                                        ))}
                                    </Fragment>
                                ))
                                .filter((_, index) => index === indexes[0])}
                        </DropdownMenuListStyled>
                    </CSSTransition>

                    <CSSTransition
                        in={activeMenu === 'tertiary'}
                        timeout={500}
                        classNames="menu-tertiary"
                        unmountOnExit
                        onEnter={calcHeight}
                    >
                        <DropdownMenuListStyled>
                            <DropdownSlideTo
                                changeState={changeState}
                                goToMenu="secondary"
                                slideTo="left"
                                type="stepBack"
                                iconText={t('Back')}
                            />
                            {navigationItems
                                .map((navigationItem, index) => (
                                    <Fragment key={index}>
                                        {navigationItem.categoriesByColumns.map((columnCategories, columnIndex) => (
                                            <Fragment key={columnIndex}>
                                                {columnCategories.categories
                                                    .map((columnCategory, columnCategoryIndex) => (
                                                        <Fragment key={columnCategoryIndex}>
                                                            <DropdownMenuListTitleStyled>
                                                                {columnCategory.name}
                                                            </DropdownMenuListTitleStyled>
                                                            {columnCategory.children.map(
                                                                (columnCategoryChild, subListIndex) => (
                                                                    <DropdownItem
                                                                        key={subListIndex}
                                                                        columnCategoryChild={columnCategoryChild}
                                                                        level="tertiary"
                                                                        variant="small"
                                                                    />
                                                                ),
                                                            )}
                                                        </Fragment>
                                                    ))
                                                    .filter(
                                                        (_, columnCategoryIndex) =>
                                                            columnCategories.columnNumber +
                                                                '-' +
                                                                columnCategoryIndex ===
                                                            indexes[1],
                                                    )}
                                            </Fragment>
                                        ))}
                                    </Fragment>
                                ))
                                .filter((_, index) => index === indexes[0])}
                        </DropdownMenuListStyled>
                    </CSSTransition>
                </DropdownMenuStyled>
            </CSSTransition>
        </DropdownMenuWrapperStyled>
    );
};

/* @component */
export default DropdownMenu;
