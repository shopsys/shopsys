import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmEventInterface } from 'gtm/types/events';
import { sha256 } from 'utils/hash/sha256';
import { isClient } from 'utils/isClient';

type EventWithUser = GtmEventInterface<GtmEventType, unknown> & {
    user?: { email?: string; emailHash?: string };
};

const addEmailHashIfNeeded = async (event: EventWithUser): Promise<EventWithUser> => {
    if (event.user?.email && !event.user.emailHash) {
        const emailHash = await sha256(event.user.email);

        if (emailHash) {
            return {
                ...event,
                user: {
                    ...event.user,
                    emailHash,
                },
            };
        }
    }
    return event;
};

// Async path: sha256() uses Web Crypto API which returns a Promise, so email hashing
// must be deferred via .then() rather than blocking the synchronous push.
export const gtmSafePushEvent = (event: GtmEventInterface<GtmEventType, unknown>): void => {
    if (!isClient) {
        return;
    }

    window.dataLayer = window.dataLayer ?? [];
    const eventWithUser = event as EventWithUser;

    if (!eventWithUser.user?.email || eventWithUser.user.emailHash) {
        window.dataLayer.push(eventWithUser);

        return;
    }

    addEmailHashIfNeeded(eventWithUser).then((eventWithHash) => {
        window.dataLayer?.push(eventWithHash);
    });
};
