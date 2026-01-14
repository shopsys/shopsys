import { captureException } from '@sentry/nextjs';

export const sha256 = async (message: string): Promise<string | null> => {
    try {
        const encoder = new TextEncoder();
        const data = encoder.encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));

        return hashArray.map((byte) => byte.toString(16).padStart(2, '0')).join('');
    } catch (error) {
        captureException(error, { level: 'info' });
        return null;
    }
};
