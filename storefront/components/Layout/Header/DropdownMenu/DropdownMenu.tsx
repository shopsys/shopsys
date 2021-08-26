import {
    DropdownMenuListStyled,
    DropdownMenuListTitleStyled,
    DropdownMenuStyled,
    DropdownMenuWrapperStyled,
} from './DropdownMenu.style';
import { Fragment, ReactElement, useState } from 'react';
import { CSSTransition } from 'react-transition-group';
import DropdownItem from './Item';
import DropdownSlideTo from './SlideTo';
import { getNavigationItems } from '../../../../connectors/navigation/Navigation';
import SubMenu from './SubMenu';
import { useTranslation } from 'react-i18next';

const DropdownMenu = (props): ReactElement | null => {
    const { t } = useTranslation();
    const navigationItems = getNavigationItems();
    const [activeMenu, setActiveMenu] = useState('main');
    const [indexes, setIndexes] = useState([]);
    const [slideTo, setSlideTo] = useState('right');
    const [menuHeight, setMenuHeight] = useState(null);

    if (navigationItems === undefined || (Array.isArray(navigationItems) && navigationItems.length === 0)) {
        return null;
    }

    const calcHeight = (el) => {
        const height = el.offsetHeight;
        setMenuHeight(height);
    };

    const changeState = (props) => {
        if (props.goToMenu !== undefined) {
            setActiveMenu(props.goToMenu);
        }

        if (props.slideTo !== undefined) {
            setSlideTo(props.slideTo);
        }

        if (props.indexes !== undefined) {
            setIndexes((oldArray) => [...oldArray, props.indexes]);
        }

        if (props.slideTo === 'left') {
            indexes.pop();

            if (indexes.length === 0) {
                setIndexes([]);
            } else {
                setIndexes([indexes]);
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
                        in={activeMenu === 'main'}
                        timeout={500}
                        classNames="menu-primary"
                        unmountOnExit
                        onEnter={calcHeight}
                    >
                        <DropdownMenuListStyled>
                            {navigationItems.map((navigationItem, index) => (
                                <DropdownItem
                                    key={index}
                                    itemData={navigationItem}
                                    changeState={changeState}
                                    indexes={index}
                                    level="main"
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
                                goToMenu="main"
                                slideTo="left"
                                variant="stepBack"
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
                                                            itemData={columnCategory}
                                                            changeState={changeState}
                                                            level="secondary"
                                                            goToMenu="third"
                                                            indexes={
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
                                .filter((item, index) => index == indexes[0])}
                        </DropdownMenuListStyled>
                    </CSSTransition>

                    <CSSTransition
                        in={activeMenu === 'third'}
                        timeout={500}
                        classNames="menu-third"
                        unmountOnExit
                        onEnter={calcHeight}
                    >
                        <DropdownMenuListStyled>
                            <DropdownSlideTo
                                changeState={changeState}
                                goToMenu="secondary"
                                slideTo="left"
                                variant="stepBack"
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
                                                                        itemData={columnCategoryChild}
                                                                        level="third"
                                                                        variant="small"
                                                                    />
                                                                ),
                                                            )}
                                                        </Fragment>
                                                    ))
                                                    .filter(
                                                        (item, columnCategoryIndex) =>
                                                            columnCategories.columnNumber + '-' + columnCategoryIndex ==
                                                            indexes[1],
                                                    )}
                                            </Fragment>
                                        ))}
                                    </Fragment>
                                ))
                                .filter((item, index) => index == indexes[0])}
                        </DropdownMenuListStyled>
                    </CSSTransition>
                </DropdownMenuStyled>
            </CSSTransition>
        </DropdownMenuWrapperStyled>
    );
};

/* @component */
export default DropdownMenu;
