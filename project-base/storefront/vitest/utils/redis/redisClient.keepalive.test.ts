import { type AddressInfo, createServer, type Socket } from 'node:net';
import { setImmediate } from 'node:timers/promises';
import { logException } from 'utils/errors/logException';
import { type AppRedisClient, getRedisClient } from 'utils/redis/redisClient';
import { expect, test, vi } from 'vitest';

vi.mock('utils/errors/logException', () => ({ logException: vi.fn() }));

const parseCommand = (buffer: Buffer) => {
    let end = buffer.indexOf('\r\n');
    if (end === -1) {
        return;
    }
    const argumentCount = Number(buffer.subarray(1, end));
    const args: string[] = [];
    let offset = end + 2;
    for (let i = 0; i < argumentCount; i++) {
        end = buffer.indexOf('\r\n', offset);
        if (end === -1) {
            return;
        }
        const length = Number(buffer.subarray(offset + 1, end));
        offset = end + 2;
        if (buffer.length < offset + length + 2) {
            return;
        }
        args.push(buffer.subarray(offset, offset + length).toString());
        offset += length + 2;
    }
    return { args, bytes: offset };
};

test('keeps the shared connection usable beyond the server idle timeout', async () => {
    vi.stubGlobal('window', undefined);
    vi.stubGlobal('__shopsysRedis', undefined);
    vi.stubEnv('SERVER_TIMING', '0');
    vi.useFakeTimers({ toFake: ['Date', 'setTimeout', 'clearTimeout'] });
    const peers = new Set<Socket>();
    const idleTimers = new Map<Socket, ReturnType<typeof setTimeout>>();
    let connections = 0;
    let idleDisconnects = 0;
    let pings = 0;
    let client: AppRedisClient | undefined;
    const server = createServer((peer) => {
        connections++;
        peers.add(peer);
        const resetIdleTimeout = () => {
            clearTimeout(idleTimers.get(peer));
            idleTimers.set(
                peer,
                setTimeout(() => {
                    idleDisconnects++;
                    peer.end();
                }, 60000),
            );
        };
        resetIdleTimeout();
        peer.on('close', () => {
            clearTimeout(idleTimers.get(peer));
            idleTimers.delete(peer);
            peers.delete(peer);
        });
        let input = Buffer.alloc(0);
        peer.on('data', (data: Buffer) => {
            input = Buffer.concat([input, data]);
            let command = parseCommand(input);
            while (command) {
                input = input.subarray(command.bytes);
                resetIdleTimeout();
                if (command.args[0] === 'PING') {
                    pings++;
                    peer.write('+PONG\r\n');
                } else if (command.args[0] === 'GET') {
                    peer.write('$6\r\ncached\r\n');
                } else {
                    peer.write('+OK\r\n');
                }
                command = parseCommand(input);
            }
        });
    });
    try {
        await new Promise<void>((resolve) => server.listen(0, '127.0.0.1', resolve));
        vi.stubEnv('REDIS_HOST', `127.0.0.1:${(server.address() as AddressInfo).port}`);
        const connection = getRedisClient(undefined, 5000).then((connectedClient) => {
            client = connectedClient;
        });
        await vi.waitFor(() => expect(client?.isReady).toBe(true));
        await connection;

        // Advance in steps so the real TCP peer can respond between simulated timer ticks.
        for (let i = 0; i < 6; i++) {
            await vi.advanceTimersByTimeAsync(15000);
            await setImmediate();
        }

        expect(idleDisconnects).toBe(0);
        expect(connections).toBe(1);
        expect(pings).toBeGreaterThan(0);
        expect(logException).not.toHaveBeenCalled();
        expect(await getRedisClient()).toBe(client);
        expect(await client!.get('translation')).toBe('cached');
    } finally {
        if (client?.isOpen) {
            await client.disconnect();
        }
        for (const timeout of Array.from(idleTimers.values())) {
            clearTimeout(timeout);
        }
        for (const peer of Array.from(peers)) {
            peer.destroy();
        }
        await new Promise<void>((resolve) => server.close(() => resolve()));
        vi.useRealTimers();
        vi.unstubAllGlobals();
        vi.unstubAllEnvs();
    }
});
