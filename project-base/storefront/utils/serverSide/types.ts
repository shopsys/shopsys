import { DocumentNode } from 'graphql';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { GetServerSidePropsContext } from 'next';
import { Translate } from 'next-translate';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { CustomerUserAreaEnum } from 'types/customer';
import { Client, OperationResult, SSRData, SSRExchange } from 'urql';
import { CookiesStoreState } from 'utils/cookies/cookiesStore';
import { DomainConfigType } from 'utils/domain/domainConfig';

export type ServerSidePropsType = {
    urqlState: SSRData;
    isMaintenance: boolean;
    isForbidden: boolean;
    customerUserRoles: TypeCustomerUserRoleEnum[];
    domainConfig: DomainConfigType;
    cookiesStore: CookiesStoreState;
    ipAddress?: string;
} & Record<string, any>;

export type QueriesArray<VariablesType> = { query: string | DocumentNode; variables?: VariablesType }[];

export type LayoutQueryResult = {
    resolvedQueries: OperationResult<any, any>[];
    seoPageSlug: string | null;
};

export type CurrentCustomerUserPrefetchMode = 'auth' | 'full';

export type InitServerSidePropsParameters<VariablesType> = {
    domainConfig: DomainConfigType;
    context: GetServerSidePropsContext;
    authenticationConfig?: {
        authenticationRequired?: boolean;
        authorizedRoles?: TypeCustomerUserRoleEnum[];
        authorizedAreas?: CustomerUserAreaEnum[];
    };
    prefetchedQueries?: QueriesArray<VariablesType>;
    currentCustomerUserPrefetchMode?: CurrentCustomerUserPrefetchMode;
    additionalProps?: Record<string, any>;
} & (
    | {
          client: Client;
          redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
          ssrExchange: SSRExchange;
          t?: never;
      }
    | {
          client?: never;
          redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
          ssrExchange?: SSRExchange;
          t: Translate;
      }
);

export type PrefetchLayoutParams<VariablesType> = {
    client: Client;
    context: GetServerSidePropsContext;
    domainConfig: DomainConfigType;
    prefetchedQueries?: QueriesArray<VariablesType>;
    currentCustomerUserPrefetchMode?: CurrentCustomerUserPrefetchMode;
};

export type BuildServerSidePropsParams = {
    layoutResult: LayoutQueryResult;
    client: Client;
    redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
    ssrExchange: SSRExchange;
    context: GetServerSidePropsContext;
    domainConfig: DomainConfigType;
    authenticationConfig?: {
        authenticationRequired?: boolean;
        authorizedRoles?: TypeCustomerUserRoleEnum[];
        authorizedAreas?: CustomerUserAreaEnum[];
    };
    additionalProps?: Record<string, any>;
    pageQueryResults?: OperationResult<any, any>[];
};
