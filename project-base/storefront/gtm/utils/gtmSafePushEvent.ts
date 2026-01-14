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

export const gtmSafePushEvent = (event: GtmEventInterface<GtmEventType, unknown>): void => {
    if (isClient) {
        window.dataLayer = window.dataLayer ?? [];

        addEmailHashIfNeeded(event as EventWithUser).then((eventWithHash) => {
            window.dataLayer?.push(eventWithHash);
        });
    }
};
