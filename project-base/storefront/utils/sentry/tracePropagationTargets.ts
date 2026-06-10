const sameOriginGraphqlPathPattern = /^\/graphql(?:[/?#]|$)/;

export function getTracePropagationTargetsFromPublicGraphqlEndpoints(
    publicGraphqlEndpoints: readonly string[],
): Array<string | RegExp> {
    const publicGraphqlEndpointTargets = publicGraphqlEndpoints
        .map((publicGraphqlEndpoint) => getTracePropagationTargetFromGraphqlEndpoint(publicGraphqlEndpoint))
        .filter((target): target is RegExp => target !== null);
    const uniquePublicGraphqlEndpointTargets = publicGraphqlEndpointTargets.filter(
        (target, index, targets) =>
            targets.findIndex((existingTarget) => existingTarget.source === target.source) === index,
    );

    return [sameOriginGraphqlPathPattern, ...uniquePublicGraphqlEndpointTargets];
}

export function getTracePropagationTargetsFromInternalEndpoint(internalEndpoint: string | undefined): RegExp[] {
    const tracePropagationTarget = getTracePropagationTargetFromInternalEndpoint(internalEndpoint);

    return tracePropagationTarget !== null ? [tracePropagationTarget] : [];
}

export function getTracePropagationTargetFromGraphqlEndpoint(graphqlEndpoint: string): RegExp | null {
    const normalizedGraphqlEndpoint = normalizeUrlWithoutTrailingSlash(graphqlEndpoint);

    if (normalizedGraphqlEndpoint === null) {
        return null;
    }

    return new RegExp(`^${escapeRegExp(normalizedGraphqlEndpoint)}(?:[/?#]|$)`);
}

export function getTracePropagationTargetFromInternalEndpoint(internalEndpoint: string | undefined): RegExp | null {
    if (!internalEndpoint) {
        return null;
    }

    const normalizedInternalEndpoint = normalizeBaseUrlWithTrailingSlash(internalEndpoint);

    if (normalizedInternalEndpoint === null) {
        return null;
    }

    return new RegExp(`^${escapeRegExp(normalizedInternalEndpoint)}(?:[^/]+/)?graphql(?:[/?#]|$)`);
}

function normalizeUrlWithoutTrailingSlash(url: string): string | null {
    try {
        const urlObject = new URL(url);

        return urlObject.href.replace(/\/$/, '');
    } catch {
        return null;
    }
}

function normalizeBaseUrlWithTrailingSlash(url: string): string | null {
    try {
        const urlObject = new URL(url);
        const urlHrefWithoutTrailingSlash = urlObject.href.replace(/\/$/, '');

        return `${urlHrefWithoutTrailingSlash}/`;
    } catch {
        return null;
    }
}

function escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
