import { IncomingMessage, ServerResponse } from 'http';
import { Socket } from 'net';
import { measureServerTiming, recordServerTiming } from 'utils/serverSide/serverTiming';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

describe('server timing', () => {
    beforeEach(() => {
        vi.stubEnv('SERVER_TIMING', '1');
    });

    afterEach(() => {
        vi.unstubAllEnvs();
    });

    test.each([undefined, '0', '', 'true'])('does not publish diagnostics when SERVER_TIMING is %s', async (value) => {
        vi.stubEnv('SERVER_TIMING', value);
        const response = new ServerResponse(new IncomingMessage(new Socket()));
        const action = vi.fn().mockResolvedValue('translated');

        recordServerTiming(response, 'cache_SettingsQuery', 1, 'hit');
        const result = await measureServerTiming(response, 'translations', action);

        expect(result).toBe('translated');
        expect(action).toHaveBeenCalledOnce();
        expect(response.getHeader('Server-Timing')).toBeUndefined();
    });

    test('preserves failures and upstream metrics when disabled', async () => {
        vi.stubEnv('SERVER_TIMING', '0');
        const response = new ServerResponse(new IncomingMessage(new Socket()));
        response.setHeader('Server-Timing', 'upstream;dur=2');
        const error = new Error('query failed');

        await expect(
            measureServerTiming(response, 'gssp', async () => {
                throw error;
            }),
        ).rejects.toBe(error);

        expect(response.getHeader('Server-Timing')).toBe('upstream;dur=2');
    });

    test('preserves existing metrics and returns the measured result', async () => {
        const response = new ServerResponse(new IncomingMessage(new Socket()));
        response.setHeader('Server-Timing', ['upstream;dur=2', 'proxy;dur=1']);
        vi.spyOn(performance, 'now').mockReturnValueOnce(100).mockReturnValueOnce(125);

        const result = await measureServerTiming(response, 'translations', async () => 'translated');

        expect(result).toBe('translated');
        expect(response.getHeader('Server-Timing')).toBe('upstream;dur=2,proxy;dur=1, translations;dur=25.0');
    });

    test('preserves failures while recording their duration', async () => {
        const response = new ServerResponse(new IncomingMessage(new Socket()));
        const error = new Error('query failed');
        vi.spyOn(performance, 'now').mockReturnValueOnce(10).mockReturnValueOnce(40);

        await expect(
            measureServerTiming(response, 'gssp', async () => {
                throw error;
            }),
        ).rejects.toBe(error);

        expect(response.getHeader('Server-Timing')).toBe('gssp;dur=30.0');
    });

    test('keeps parallel requests isolated and includes cache outcomes', () => {
        const firstResponse = new ServerResponse(new IncomingMessage(new Socket()));
        const secondResponse = new ServerResponse(new IncomingMessage(new Socket()));

        recordServerTiming(firstResponse, 'cache_SettingsQuery', 1, 'hit');
        recordServerTiming(secondResponse, 'cache_SettingsQuery', 2, 'miss');

        expect(firstResponse.getHeader('Server-Timing')).toBe('cache_SettingsQuery;dur=1.0;desc="hit"');
        expect(secondResponse.getHeader('Server-Timing')).toBe('cache_SettingsQuery;dur=2.0;desc="miss"');
    });

    test('does not modify a response after headers have been sent', () => {
        const response = new ServerResponse(new IncomingMessage(new Socket()));
        response.writeHead(200);

        expect(() => recordServerTiming(response, 'late_query', 5)).not.toThrow();
        expect(response.getHeader('Server-Timing')).toBeUndefined();
    });
});
