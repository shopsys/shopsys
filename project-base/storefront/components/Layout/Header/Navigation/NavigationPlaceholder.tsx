import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { twJoin } from 'tailwind-merge';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import { NavigationProps } from './Navigation';

const SKELETON_COUNT = 5;

type NavigationPlaceholderProps = {
    navigation?: NavigationProps['navigation'];
};

export const NavigationPlaceholder: FC<NavigationPlaceholderProps> = ({ navigation }) => (
    <nav aria-hidden={navigation?.length ? undefined : true} id="main-navigation">
        <ul className="relative hidden w-full overflow-hidden lg:flex">
            {navigation?.length
                ? navigation.map((navigationItem, index) => {
                      const hasChildren = !!navigationItem.categoriesByColumns.length;

                      return (
                          <li key={index} className="group">
                              <ExtendedNextLink
                                  href={navigationItem.link}
                                  skeletonType={getPageTypeKey(navigationItem.routeName) || DEFAULT_SKELETON_TYPE}
                                  className={twJoin(
                                      'relative m-0 flex items-center whitespace-nowrap p-5 font-bold font-secondary text-sm vl:text-base group-first-of-type:pl-0',
                                      'text-link-inverted-default no-underline',
                                      'hover:text-link-inverted-hovered hover:no-underline group-hover:text-link-inverted-hovered group-hover:no-underline',
                                      'active:text-link-inverted-hovered',
                                      'disabled:text-link-inverted-disabled',
                                  )}
                              >
                                  {navigationItem.name}
                                  {hasChildren && (
                                      <ArrowIcon className="ml-2 size-5 text-link-inverted-default group-hover:rotate-180 group-hover:text-link-inverted-hovered" />
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
