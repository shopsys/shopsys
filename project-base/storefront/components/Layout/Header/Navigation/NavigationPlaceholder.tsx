import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import type { NavigationProps } from './Navigation';

const SKELETON_COUNT = 5;

type NavigationPlaceholderProps = {
    navigation?: NavigationProps['navigation'];
};

export const NavigationPlaceholder: FC<NavigationPlaceholderProps> = ({ navigation }) => {
    return (
        <nav aria-hidden={navigation?.length ? undefined : true} id="main-navigation" tabIndex={-1}>
            <ul className="mask-[linear-gradient(to_right,black_calc(100%-32px),transparent_100%)] relative hidden w-full overflow-hidden lg:flex">
                {navigation?.length
                    ? navigation.map((navigationItem, index) => {
                          const hasChildren = !!navigationItem.categoriesByColumns.length;

                          return (
                              <li key={index} className="group">
                                  <ExtendedNextLink
                                      className="relative m-0 flex items-center whitespace-nowrap p-5 font-secondary font-semibold text-link-inverted-default text-sm vl:text-base no-underline hover:text-link-inverted-hovered hover:no-underline active:text-link-inverted-hovered disabled:text-link-inverted-disabled group-first-of-type:pl-0 group-last-of-type:pr-0 group-hover:text-link-inverted-hovered group-hover:no-underline"
                                      href={navigationItem.link}
                                      skeletonType={getPageTypeKey(navigationItem.routeName) || DEFAULT_SKELETON_TYPE}
                                  >
                                      {navigationItem.name}
                                      {hasChildren && (
                                          <ArrowIcon className="ml-1 size-5 text-link-inverted-default group-hover:rotate-180 group-hover:text-link-inverted-hovered" />
                                      )}
                                  </ExtendedNextLink>
                              </li>
                          );
                      })
                    : [...Array(SKELETON_COUNT)].map((_, index) => (
                          <li key={index} className="relative m-0 flex items-center p-5 first:pl-0">
                              <Skeleton className="h-6 w-28 bg-skeleton-default" />
                          </li>
                      ))}
            </ul>
        </nav>
    );
};
