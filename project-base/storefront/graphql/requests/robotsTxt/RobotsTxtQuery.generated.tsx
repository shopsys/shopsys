// @ts-nocheck
import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeRobotsTxtQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeRobotsTxtQuery = (
  { __typename?: 'Query' }
  & { settings: Types.Maybe<(
    { __typename?: 'Settings' }
    & { seo: (
      { __typename?: 'SeoSetting' }
      & Pick<Types.TypeSeoSetting, 'robotsTxtContent'>
    ) }
  )> }
);


export const RobotsTxtQueryDocument = gql`
    query RobotsTxtQuery {
  settings {
    seo {
      robotsTxtContent
    }
  }
}
    `;

export function useRobotsTxtQuery(options?: Omit<Urql.UseQueryArgs<TypeRobotsTxtQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeRobotsTxtQuery, TypeRobotsTxtQueryVariables>({ query: RobotsTxtQueryDocument, ...options });
};