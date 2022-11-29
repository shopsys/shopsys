export type PaginationState = {
    page: number;
    endCursor?: string;
};

export type PaginationCallbacks = {
    setPage: number;
    setEndCursor: string;
    setIsLoadMore: boolean;
    setPagination: PaginationState;
    resetPagination: never;
};

export type PaginationActions<Actions = PaginationCallbacks> = {
    [K in keyof Actions]: Actions[K] extends never
        ? {
              type: K;
          }
        : {
              type: K;
              payload: Actions[K];
          };
}[keyof Actions];
