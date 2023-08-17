import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Icon } from 'components/Basic/Icon/Icon';
import { NavigationLeaf } from 'components/Layout/Header/Navigation/NavigationLeaf';
import { CategoriesByColumnFragmentApi } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { twJoin } from 'tailwind-merge';

type NavigationItemProps = {
    navigationItem: CategoriesByColumnFragmentApi;
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationitem';

export const NavigationItem: FC<NavigationItemProps> = (props) => {
    const hasChildren = props.navigationItem.categoriesByColumns.length > 0;

    return (
        <li className="group inline-block p-0 align-middle last:mr-0 lg:mr-6 xl:mr-12" data-testid={TEST_IDENTIFIER}>
            <ExtendedNextLink
                type="category"
                href={props.navigationItem.link}
                className={twJoin(
                    'relative m-0 block px-2 py-4 text-sm font-bold uppercase text-white no-underline after:absolute after:bottom-0 after:left-0 after:right-0 after:hidden after:h-1 after:bg-orange after:content-[""] hover:text-orangeLight hover:no-underline hover:after:block group-hover:text-orangeLight group-hover:no-underline after:group-hover:block vl:text-base',
                )}
            >
                <>
                    {props.navigationItem.name}
                    {hasChildren && (
                        <Icon
                            iconType="icon"
                            icon="Arrow"
                            className="ml-2 text-white group-hover:rotate-180 group-hover:text-orangeLight"
                        />
                    )}
                </>
            </ExtendedNextLink>
            {hasChildren && (
                <div className="pointer-events-none absolute left-0 right-0 z-menu block bg-white py-12 px-14 opacity-0 shadow-md group-hover:pointer-events-auto group-hover:opacity-100">
                    <div className="-ml-11 flex">
                        <NavigationLeaf columnCategories={props.navigationItem.categoriesByColumns} />
                    </div>
                </div>
            )}
        </li>
    );
};
