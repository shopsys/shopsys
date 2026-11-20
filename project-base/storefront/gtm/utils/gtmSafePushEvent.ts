import { captureException } from '@sentry/nextjs';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmEventInterface } from 'gtm/types/events';
import { sha256 } from 'utils/hash/sha256';
import { isClient } from 'utils/isClient';

type EventWithUser = GtmEventInterface<GtmEventType, unknown> & {
    user?: { email?: string; emailHash?: string };
};

let pendingEventsPush: Promise<void> | null = null;

const reportFailedEventPush = (error: unknown) => {
    try {
        captureException(error);
    } catch {
        // error reporting must not reject the queue again
    }
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

const pushEventAfterPendingEvents = (event: EventWithUser): void => {
    const previousEventsPush = pendingEventsPush;

    const pushEvent = async () => {
        await previousEventsPush;
        const eventWithHash = await addEmailHashIfNeeded(event);
        window.dataLayer?.push(eventWithHash);
    };

    // a failed push must not block the events queued after it, e.g. a consent update
    const eventPush = pushEvent().catch(reportFailedEventPush);
    pendingEventsPush = eventPush;

    const releasePendingEventsPush = () => {
        if (pendingEventsPush === eventPush) {
            pendingEventsPush = null;
        }
    };

    eventPush.then(releasePendingEventsPush);
};

export const gtmSafePushEvent = (event: GtmEventInterface<GtmEventType, unknown>): void => {
    if (isClient) {
        window.dataLayer = window.dataLayer ?? [];
        const eventWithUser = event as EventWithUser;
        const isEmailHashNeeded = !!eventWithUser.user?.email && !eventWithUser.user.emailHash;

        // an event must not overtake an earlier one that still waits for its email hash (page_ready has to come first)
        if (isEmailHashNeeded || pendingEventsPush) {
            pushEventAfterPendingEvents(eventWithUser);

            return;
        }

        window.dataLayer.push(event);
    }
};
