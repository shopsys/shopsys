import { headers } from 'next/headers';
import 'server-only';

/**
 * Extracts request information from Next.js headers
 *
 * @return Object containing host and pathname from request
 */
export async function getRequestInfo(): Promise<{ host: string; pathname: string }> {
    const headersList = await headers();
    const host = headersList.get('host');

    if (!host) {
        throw new Error('Host header not found in request');
    }

    // Try to get pathname from x-invoke-path (Next.js internal) or x-pathname (custom)
    // Fallback to '/' if not available
    const pathname = headersList.get('x-invoke-path') || headersList.get('x-pathname') || '/';

    return { host, pathname };
}
