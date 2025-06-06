import { NextRequest, NextResponse } from 'next/server';
import { logException } from 'utils/errors/logException';

export async function POST(request: NextRequest) {
    try {
        const body = await request.json();

        if (typeof body === 'object' && 'exception' in body) {
            logException({ error: body, location: 'log-exception API exception handler' });
        }

        return NextResponse.json({ status: 'ok' });
    } catch {
        // If parsing JSON fails, still return ok status
        return NextResponse.json({ status: 'ok' });
    }
}
