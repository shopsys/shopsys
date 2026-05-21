import type { PageType } from 'store/slices/createPageLoadingStateSlice';
import { getSkeletonTypeFromRouteUrl } from './getSkeletonTypeFromRouteUrl';

const HISTORY_STATE_SKELETON_TYPE_KEY = 'shopsysSkeletonType';

type RouteStateWithSkeletonType = {
    as?: string;
    url?: string;
    [HISTORY_STATE_SKELETON_TYPE_KEY]?: PageType;
};

export const getSkeletonTypeFromRouteState = (routeState: RouteStateWithSkeletonType): PageType | undefined => {
    return (
        routeState[HISTORY_STATE_SKELETON_TYPE_KEY] ??
        getSkeletonTypeFromRouteUrl(routeState.url) ??
        getSkeletonTypeFromRouteUrl(routeState.as)
    );
};

export const updateCurrentHistoryStateSkeletonType = (skeletonType: PageType | undefined): void => {
    const currentState = window.history.state;

    if (!currentState) {
        return;
    }

    const newState = { ...currentState };

    if (skeletonType) {
        newState[HISTORY_STATE_SKELETON_TYPE_KEY] = skeletonType;
    } else {
        delete newState[HISTORY_STATE_SKELETON_TYPE_KEY];
    }

    window.history.replaceState(newState, '', window.location.href);
};
