import { FriendlyPagesTypesKey } from './friendlyUrl';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';

export type ListedItemPropTypeTypename = 'ArticleSite' | 'BlogArticle' | 'Category' | 'Brand' | 'Link';

export type ListedItemPropType = (
    | {
          slug: string;
          mainImage: TypeImageFragment;
          name: string;
          totalCount?: number;
      }
    | {
          slug: string;
          mainImage: TypeImageFragment;
          name: string;
      }
    | {
          slug: string;
          name: string;
      }
    | {
          slug: string;
          name: string;
          icon: React.ReactNode;
      }
) & {
    __typename?: ListedItemPropTypeTypename;
};

export const CUSTOM_PAGE_TYPES = [
    'cart',
    'comparison',
    'contact-information',
    'homepage',
    'order-confirmation',
    'orderDetail',
    'orderList',
    'complaintNew',
    'complaintDetail',
    'complaintList',
    'editProfile',
    'changePassword',
    'account',
    'productMainVariant',
    'registration',
    'stores',
    'transport-and-payment',
    'contact-information',
    'cart',
    'order-confirmation',
    'contact',
    'wishlist',
    'customer-users',
    'user-consent',
] as const;

export type PageType = FriendlyPagesTypesKey | (typeof CUSTOM_PAGE_TYPES)[number];
