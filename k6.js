import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '20s', target: 10 }, // Ramp up to 10, 25, 50, 75, 100 users
        { duration: '25s', target: 20 }, // Stay at 20, 50, 100, 150, 200 users
        { duration: '15s', target: 0 }, // Ramp down
    ],
    // thresholds: {
    //     http_req_duration: ['p(95)<5000'], // Relaxed threshold for higher load
    //     http_req_failed: ['rate<0.1'],
    // },
};

// const baseUrl = 'http://127.0.0.1:8000';
// const baseUrl = 'https://17-0.odin.shopsys.cloud';
// const baseUrl = 'https://ssfwcc.prod.shopsys.cloud';
// const baseUrl = 'https://ssfwcc.dev.shopsys.cloud/';
const baseUrl = 'https://ssfwcc-alpha.dev.shopsys.cloud';

const scenarios = [
    { name: 'Homepage', url: '', weight: 25 },
    // { name: 'Category', url: '/electronics', weight: 25 },
    // { name: 'Product', url: '/television-22-sencor-sle-22f46dm4-hello-kitty-plasma', weight: 25 },
    // { name: 'Search', url: '/search?q=television', weight: 25 },
];

export default function () {
    const scenario = scenarios[Math.floor(Math.random() * scenarios.length)];

    const response = http.get(`${baseUrl}${scenario.url}`, {
        headers: {
            'User-Agent': 'K6LoadTest/1.0',
        },
        tags: {
            page: scenario.name.toLowerCase(),
            scenario: scenario.name,
        },
    });

    check(response, {
        [`${scenario.name}: Status OK`]: (r) => r.status === 200,
        [`${scenario.name}: Response time OK`]: (r) => r.timings.duration < 5000,
    });

    sleep(1);
}

function formatMetricValue(value, unit = 'ms') {
    if (value === null || value === undefined || value === 0) return '-';
    if (unit === 'ms') return `${Math.round(value * 1000) / 1000}`;
    if (unit === '%') return `${(value * 100).toFixed(2)}%`;
    if (unit === 's') return `${Math.round(value * 1000) / 1000}s`;
    return `${Math.round(value * 1000) / 1000}`;
}

function getPerformanceGrade(value, thresholds) {
    if (value <= thresholds.excellent) return { grade: 'A+', color: '#10b981', text: 'Excellent', bgColor: '#dcfce7' };
    if (value <= thresholds.good) return { grade: 'A', color: '#059669', text: 'Good', bgColor: '#bbf7d0' };
    if (value <= thresholds.fair) return { grade: 'B', color: '#f59e0b', text: 'Fair', bgColor: '#fef3c7' };
    if (value <= thresholds.poor) return { grade: 'C', color: '#ef4444', text: 'Poor', bgColor: '#fecaca' };
    return { grade: 'F', color: '#dc2626', text: 'Critical', bgColor: '#fca5a5' };
}

function getTtfbGrade(ttfbMs) {
    if (ttfbMs < 100) return { grade: 'A+', text: 'Lightning', color: '#10b981' };
    if (ttfbMs < 300) return { grade: 'A', text: 'Fast', color: '#22c55e' };
    if (ttfbMs < 500) return { grade: 'B', text: 'Acceptable', color: '#f59e0b' };
    if (ttfbMs < 1000) return { grade: 'C', text: 'Slow', color: '#ef4444' };
    return { grade: 'F', text: 'Critical', color: '#dc2626' };
}

export function handleSummary(data) {
    const timestamp = new Date().toISOString();
    const metrics = data.metrics;

    // Extract all the metric values
    const httpReqDuration = metrics.http_req_duration?.values || {};
    const httpReqWaiting = metrics.http_req_waiting?.values || {};
    const httpReqConnecting = metrics.http_req_connecting?.values || {};
    const httpReqTlsHandshaking = metrics.http_req_tls_handshaking?.values || {};
    const httpReqSending = metrics.http_req_sending?.values || {};
    const httpReqReceiving = metrics.http_req_receiving?.values || {};
    const httpReqBlocked = metrics.http_req_blocked?.values || {};
    const iterationDuration = metrics.iteration_duration?.values || {};
    const httpReqs = metrics.http_reqs?.values || {};
    const httpReqFailed = metrics.http_req_failed?.values || {};
    const vus = metrics.vus?.values || {};
    const vusMax = metrics.vus_max?.values || {};
    const dataReceived = metrics.data_received?.values || {};
    const dataSent = metrics.data_sent?.values || {};

    // Performance grades
    const avgGrade = getPerformanceGrade(httpReqDuration.avg || 0, {
        excellent: 500,
        good: 1000,
        fair: 2000,
        poor: 5000,
    });
    const p95Grade = getPerformanceGrade(httpReqDuration['p(95)'] || 0, {
        excellent: 800,
        good: 1500,
        fair: 3000,
        poor: 8000,
    });
    const errorGrade = getPerformanceGrade((httpReqFailed.rate || 0) * 100, {
        excellent: 0.1,
        good: 1,
        fair: 3,
        poor: 10,
    });

    // Structure JSON data for export
    const jsonData = {
        meta: {
            timestamp: timestamp,
            testDuration: data.state?.testRunDurationMs || 0,
            baseUrl: baseUrl,
            scenarios: scenarios,
            options: options,
            version: '1.0.0',
        },
        summary: {
            totalRequests: httpReqs.count || 0,
            errorRate: httpReqFailed.rate || 0,
            errorRatePercentage: (httpReqFailed.rate || 0) * 100,
            avgResponseTime: httpReqDuration.avg || 0,
            throughput: httpReqs.rate || 0,
            peakUsers: vusMax.max || 0,
            dataReceived: {
                bytes: dataReceived.count || 0,
                megabytes: Math.round(((dataReceived.count || 0) / 1024 / 1024) * 100) / 100,
            },
            dataSent: {
                bytes: dataSent.count || 0,
                kilobytes: Math.round(((dataSent.count || 0) / 1024) * 100) / 100,
            },
        },
        metrics: {
            http_req_duration: {
                name: 'Total Request Duration',
                description: 'Complete time from sending request to receiving full response',
                unit: 'ms',
                values: httpReqDuration,
            },
            http_req_waiting: {
                name: 'Time to First Byte (TTFB)',
                description: 'Server processing time - waiting for response to start',
                unit: 'ms',
                values: httpReqWaiting,
            },
            http_req_connecting: {
                name: 'Connection Time',
                description: 'Time to establish TCP connection',
                unit: 'ms',
                values: httpReqConnecting,
            },
            http_req_tls_handshaking: {
                name: 'TLS Handshaking Time',
                description: 'Time spent on TLS handshake',
                unit: 'ms',
                values: httpReqTlsHandshaking,
            },
            http_req_sending: {
                name: 'Request Send Time',
                description: 'Time to send request to server',
                unit: 'ms',
                values: httpReqSending,
            },
            http_req_receiving: {
                name: 'Response Download Time',
                description: 'Time to download response body',
                unit: 'ms',
                values: httpReqReceiving,
            },
            http_req_blocked: {
                name: 'DNS Lookup Time',
                description: 'Time spent on DNS resolution and connection setup',
                unit: 'ms',
                values: httpReqBlocked,
            },
            iteration_duration: {
                name: 'Full Test Cycle Time',
                description: 'Complete time for one user iteration including request + think time',
                unit: 'ms',
                values: iterationDuration,
            },
            http_reqs: {
                name: 'HTTP Requests',
                description: 'Total number of HTTP requests made',
                unit: 'count/rate',
                values: httpReqs,
            },
            http_req_failed: {
                name: 'Failed Requests',
                description: 'Percentage of requests that failed (4xx/5xx status codes)',
                unit: 'rate',
                values: httpReqFailed,
            },
            vus: {
                name: 'Virtual Users',
                description: 'Number of active virtual users during test',
                unit: 'count',
                values: vus,
            },
            vus_max: {
                name: 'Peak Virtual Users',
                description: 'Maximum number of virtual users during test',
                unit: 'count',
                values: vusMax,
            },
            data_received: {
                name: 'Data Received',
                description: 'Total amount of data received from server',
                unit: 'bytes',
                values: dataReceived,
            },
            data_sent: {
                name: 'Data Sent',
                description: 'Total amount of data sent to server',
                unit: 'bytes',
                values: dataSent,
            },
        },
        performance: {
            grades: {
                averageResponseTime: {
                    value: httpReqDuration.avg || 0,
                    grade: avgGrade.grade,
                    text: avgGrade.text,
                    color: avgGrade.color,
                    thresholds: {
                        excellent: '< 500ms',
                        good: '500ms - 1s',
                        fair: '1s - 2s',
                        poor: '2s - 5s',
                        critical: '> 5s',
                    },
                },
                p95ResponseTime: {
                    value: httpReqDuration['p(95)'] || 0,
                    grade: p95Grade.grade,
                    text: p95Grade.text,
                    color: p95Grade.color,
                    thresholds: {
                        excellent: '< 800ms',
                        good: '800ms - 1.5s',
                        fair: '1.5s - 3s',
                        poor: '3s - 8s',
                        critical: '> 8s',
                    },
                },
                ttfb: {
                    value: httpReqWaiting.avg || 0,
                    grade: getTtfbGrade(httpReqWaiting.avg || 0).grade,
                    text: getTtfbGrade(httpReqWaiting.avg || 0).text,
                    color: getTtfbGrade(httpReqWaiting.avg || 0).color,
                    thresholds: {
                        excellent: '< 100ms',
                        good: '100ms - 300ms',
                        fair: '300ms - 500ms',
                        poor: '500ms - 1s',
                        critical: '> 1s',
                    },
                },
                errorRate: {
                    value: (httpReqFailed.rate || 0) * 100,
                    grade: errorGrade.grade,
                    text: errorGrade.text,
                    color: errorGrade.color,
                    thresholds: {
                        excellent: '< 0.1%',
                        good: '0.1% - 1%',
                        fair: '1% - 3%',
                        poor: '3% - 10%',
                        critical: '> 10%',
                    },
                },
            },
        },
        rawData: {
            allMetrics: metrics,
            testState: data.state || {},
            rootGroup: data.root_group || {},
        },
    };

    const htmlReport = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete K6 Performance Analysis</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .test-summary {
            background: #f8fafc;
            padding: 30px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .summary-value {
            font-size: 2rem;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .summary-label {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .metrics-section {
            padding: 40px;
        }
        
        .section-title {
            font-size: 2rem;
            color: #1e40af;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .metrics-table {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .table-header {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 20px;
        }
        
        .table-header h3 {
            color: #1f2937;
            font-size: 1.4rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }
        
        tr:hover {
            background: #f9fafb;
        }
        
        .metric-name {
            font-weight: 600;
            color: #1f2937;
        }
        
        .metric-description {
            background: #f0f9ff;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        
        .metric-description h4 {
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .metric-description p {
            color: #4b5563;
            margin-bottom: 10px;
        }
        
        .benchmarks {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .benchmark {
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .excellent { background: #dcfce7; color: #166534; }
        .good { background: #bbf7d0; color: #15803d; }
        .fair { background: #fef3c7; color: #a16207; }
        .poor { background: #fecaca; color: #b91c1c; }
        .critical { background: #fca5a5; color: #991b1b; }
        
        .performance-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .performance-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        
        .performance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .grade-badge {
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .performance-value {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .chart-container {
            background: white;
            margin: 30px 0;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .footer {
            background: #1f2937;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .performance-cards { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
            .benchmarks { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Complete K6 Performance Analysis</h1>
            <div style="font-size: 1.2rem; opacity: 0.9; margin-top: 10px;">
                Next.js Load Test Results with Detailed Metrics Explanation
            </div>
        </div>

        <div class="test-summary">
            <h2 style="color: #1e40af; margin-bottom: 20px;">📊 Test Summary</h2>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-value">${formatMetricValue(httpReqs.count, '')}</div>
                    <div class="summary-label">Total Requests</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value">${formatMetricValue(httpReqFailed.rate, '%')}</div>
                    <div class="summary-label">Error Rate</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value">${formatMetricValue(httpReqDuration.avg)}</div>
                    <div class="summary-label">Avg Response (ms)</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value">${formatMetricValue(httpReqs.rate, '/s')}</div>
                    <div class="summary-label">Throughput (req/s)</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value">${vusMax.max || 0}</div>
                    <div class="summary-label">Peak Users</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value">${Math.round((dataReceived.count || 0) / 1024 / 1024)}MB</div>
                    <div class="summary-label">Data Received</div>
                </div>
            </div>
        </div>

        <div class="metrics-section">
            <h2 class="section-title">🎯 Key Performance Indicators</h2>
            
            <div class="performance-cards">
                <div class="performance-card">
                    <div class="performance-header">
                        <h3>Average Response Time</h3>
                        <div class="grade-badge" style="background-color: ${avgGrade.color}">
                            ${avgGrade.grade} - ${avgGrade.text}
                        </div>
                    </div>
                    <div class="performance-value" style="color: ${avgGrade.color}">
                        ${formatMetricValue(httpReqDuration.avg)}ms
                    </div>
                    <div class="metric-description">
                        <h4>🕐 What This Means</h4>
                        <p>The average time it takes your server to respond to requests. This includes server processing, database queries, and content generation.</p>
                        <div class="benchmarks">
                            <div class="benchmark excellent">Excellent &lt; 500ms</div>
                            <div class="benchmark good">Good 500ms-1s</div>
                            <div class="benchmark fair">Fair 1s-2s</div>
                            <div class="benchmark poor">Poor 2s-5s</div>
                            <div class="benchmark critical">Critical &gt; 5s</div>
                        </div>
                    </div>
                </div>

                <div class="performance-card">
                    <div class="performance-header">
                        <h3>95th Percentile</h3>
                        <div class="grade-badge" style="background-color: ${p95Grade.color}">
                            ${p95Grade.grade} - ${p95Grade.text}
                        </div>
                    </div>
                    <div class="performance-value" style="color: ${p95Grade.color}">
                        ${formatMetricValue(httpReqDuration['p(95)'])}ms
                    </div>
                    <div class="metric-description">
                        <h4>📈 What This Means</h4>
                        <p>95% of your users experience response times at or below this value. This represents your worst-case performance for most users.</p>
                        <div class="benchmarks">
                            <div class="benchmark excellent">Instant &lt; 800ms</div>
                            <div class="benchmark good">Fast 800ms-1.5s</div>
                            <div class="benchmark fair">Noticeable 1.5s-3s</div>
                            <div class="benchmark poor">Slow 3s-8s</div>
                            <div class="benchmark critical">Frustrating &gt; 8s</div>
                        </div>
                    </div>
                </div>

                <div class="performance-card">
                    <div class="performance-header">
                        <h3>⚡ Time to First Byte (TTFB)</h3>
                        <div class="grade-badge" style="background-color: ${getTtfbGrade(httpReqWaiting.avg).color}">
                            ${getTtfbGrade(httpReqWaiting.avg).grade} - ${getTtfbGrade(httpReqWaiting.avg).text}
                        </div>
                    </div>
                    <div class="performance-value" style="color: ${getTtfbGrade(httpReqWaiting.avg).color}">
                        ${formatMetricValue(httpReqWaiting.avg)}ms
                    </div>
                    <div class="metric-description">
                        <h4>🏃‍♂️ What This Means</h4>
                        <p><strong>Server response speed</strong> - Time for server to start sending data after receiving your request. This is pure server processing time (database queries, API calls, rendering).</p>
                        <div class="benchmarks">
                            <div class="benchmark excellent">Lightning &lt; 100ms</div>
                            <div class="benchmark good">Fast 100-300ms</div>
                            <div class="benchmark fair">Acceptable 300-500ms</div>
                            <div class="benchmark poor">Slow 500ms-1s</div>
                            <div class="benchmark critical">Critical &gt; 1s</div>
                        </div>
                        <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 15px; margin-top: 15px; border-radius: 6px;">
                            <h5 style="color: #1e40af; margin-bottom: 8px;">💡 How to Improve TTFB:</h5>
                            <ul style="color: #1e40af; font-size: 0.9rem; line-height: 1.6; padding-left: 20px;">
                                <li><strong>Database optimization:</strong> Add indexes, optimize queries, connection pooling</li>
                                <li><strong>Server-side caching:</strong> Redis, in-memory cache for frequent data</li>
                                <li><strong>API optimization:</strong> Reduce external API calls, implement timeouts</li>
                                <li><strong>Server resources:</strong> Increase CPU/memory allocation</li>
                                <li><strong>Code optimization:</strong> Remove blocking operations, async processing</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="performance-card">
                    <div class="performance-header">
                        <h3>Error Rate</h3>
                        <div class="grade-badge" style="background-color: ${errorGrade.color}">
                            ${errorGrade.grade} - ${errorGrade.text}
                        </div>
                    </div>
                    <div class="performance-value" style="color: ${errorGrade.color}">
                        ${formatMetricValue(httpReqFailed.rate, '%')}
                    </div>
                    <div class="metric-description">
                        <h4>⚠️ What This Means</h4>
                        <p>Percentage of requests that failed (4xx/5xx status codes). This directly impacts user experience and SEO rankings.</p>
                        <div class="benchmarks">
                            <div class="benchmark excellent">Perfect &lt; 0.1%</div>
                            <div class="benchmark good">Excellent 0.1%-1%</div>
                            <div class="benchmark fair">Acceptable 1%-3%</div>
                            <div class="benchmark poor">Concerning 3%-10%</div>
                            <div class="benchmark critical">Critical &gt; 10%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="metrics-table">
                <div class="table-header">
                    <h3>📋 Complete Metrics Breakdown</h3>
                    <p style="color: #6b7280; margin-top: 5px;">All timing measurements are in milliseconds unless noted</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Count</th>
                            <th>Rate</th>
                            <th>Average</th>
                            <th>Maximum</th>
                            <th>Median</th>
                            <th>Minimum</th>
                            <th>90th %ile</th>
                            <th style="background: #dcfce7;">95th %ile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="metric-name">http_req_duration</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${formatMetricValue(httpReqDuration.avg)}</td>
                            <td>${formatMetricValue(httpReqDuration.max)}</td>
                            <td>${formatMetricValue(httpReqDuration.med)}</td>
                            <td>${formatMetricValue(httpReqDuration.min)}</td>
                            <td>${formatMetricValue(httpReqDuration['p(90)'])}</td>
                            <td style="background: #dcfce7; font-weight: bold;">${formatMetricValue(httpReqDuration['p(95)'])}</td>
                        </tr>
                        <tr>
                            <td class="metric-name">http_req_waiting (TTFB)</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${formatMetricValue(httpReqWaiting.avg)}</td>
                            <td>${formatMetricValue(httpReqWaiting.max)}</td>
                            <td>${formatMetricValue(httpReqWaiting.med)}</td>
                            <td>${formatMetricValue(httpReqWaiting.min)}</td>
                            <td>${formatMetricValue(httpReqWaiting['p(90)'])}</td>
                            <td>${formatMetricValue(httpReqWaiting['p(95)'])}</td>
                        </tr>
                        <tr>
                            <td class="metric-name">http_req_connecting</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${formatMetricValue(httpReqConnecting.avg)}</td>
                            <td>${formatMetricValue(httpReqConnecting.max)}</td>
                            <td>${formatMetricValue(httpReqConnecting.med)}</td>
                            <td>${formatMetricValue(httpReqConnecting.min)}</td>
                            <td>${formatMetricValue(httpReqConnecting['p(90)'])}</td>
                            <td>${formatMetricValue(httpReqConnecting['p(95)'])}</td>
                        </tr>
                        <tr>
                            <td class="metric-name">http_req_sending</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${formatMetricValue(httpReqSending.avg)}</td>
                            <td>${formatMetricValue(httpReqSending.max)}</td>
                            <td>${formatMetricValue(httpReqSending.med)}</td>
                            <td>${formatMetricValue(httpReqSending.min)}</td>
                            <td>${formatMetricValue(httpReqSending['p(90)'])}</td>
                            <td>${formatMetricValue(httpReqSending['p(95)'])}</td>
                        </tr>
                        <tr>
                            <td class="metric-name">http_req_receiving</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${formatMetricValue(httpReqReceiving.avg)}</td>
                            <td>${formatMetricValue(httpReqReceiving.max)}</td>
                            <td>${formatMetricValue(httpReqReceiving.med)}</td>
                            <td>${formatMetricValue(httpReqReceiving.min)}</td>
                            <td>${formatMetricValue(httpReqReceiving['p(90)'])}</td>
                            <td>${formatMetricValue(httpReqReceiving['p(95)'])}</td>
                        </tr>
                        <tr>
                            <td class="metric-name">http_req_blocked</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${formatMetricValue(httpReqBlocked.avg)}</td>
                            <td>${formatMetricValue(httpReqBlocked.max)}</td>
                            <td>${formatMetricValue(httpReqBlocked.med)}</td>
                            <td>${formatMetricValue(httpReqBlocked.min)}</td>
                            <td>${formatMetricValue(httpReqBlocked['p(90)'])}</td>
                            <td>${formatMetricValue(httpReqBlocked['p(95)'])}</td>
                        </tr>
                        <tr>
                            <td class="metric-name">iteration_duration</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${formatMetricValue(iterationDuration.avg)}</td>
                            <td>${formatMetricValue(iterationDuration.max)}</td>
                            <td>${formatMetricValue(iterationDuration.med)}</td>
                            <td>${formatMetricValue(iterationDuration.min)}</td>
                            <td>${formatMetricValue(iterationDuration['p(90)'])}</td>
                            <td>${formatMetricValue(iterationDuration['p(95)'])}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px;">
                <div class="metric-description">
                    <h4>🔍 http_req_duration</h4>
                    <p><strong>Total request time</strong> - Complete time from sending request to receiving full response. This is what users experience.</p>
                    <div style="background: #f0fdf4; border-left: 4px solid #22c55e; padding: 15px; margin-top: 15px; border-radius: 6px;">
                        <h5 style="color: #15803d; margin-bottom: 8px;">💡 How to Improve:</h5>
                        <ul style="color: #166534; font-size: 0.9rem; line-height: 1.6; padding-left: 20px;">
                            <li><strong>Enable Next.js caching:</strong> Use fetch() with cache options</li>
                            <li><strong>Optimize images:</strong> Use next/image with WebP/AVIF formats</li>
                            <li><strong>Add CDN:</strong> Serve static assets from edge locations</li>
                            <li><strong>Database optimization:</strong> Add indexes, connection pooling</li>
                            <li><strong>Code splitting:</strong> Use dynamic imports for large components</li>
                        </ul>
                    </div>
                </div>
                <div class="metric-description">
                    <h4>⏱️ http_req_waiting (TTFB)</h4>
                    <p><strong>Server processing time</strong> - Time spent waiting for server response. Measures your backend performance.</p>
                    <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-top: 15px; border-radius: 6px;">
                        <h5 style="color: #a16207; margin-bottom: 8px;">💡 How to Improve:</h5>
                        <ul style="color: #92400e; font-size: 0.9rem; line-height: 1.6; padding-left: 20px;">
                            <li><strong>Database queries:</strong> Add indexes, optimize N+1 queries</li>
                            <li><strong>Server Components:</strong> Move data fetching to server side</li>
                            <li><strong>Caching layers:</strong> Implement Redis for frequent queries</li>
                            <li><strong>API optimization:</strong> Reduce payload size, batch requests</li>
                            <li><strong>Resource allocation:</strong> Increase server CPU/memory</li>
                        </ul>
                    </div>
                </div>
                <div class="metric-description">
                    <h4>🔗 http_req_connecting</h4>
                    <p><strong>Connection time</strong> - Time to establish TCP connection. High values indicate network or server capacity issues.</p>
                    <div style="background: #e0f2fe; border-left: 4px solid #0284c7; padding: 15px; margin-top: 15px; border-radius: 6px;">
                        <h5 style="color: #0369a1; margin-bottom: 8px;">💡 How to Improve:</h5>
                        <ul style="color: #075985; font-size: 0.9rem; line-height: 1.6; padding-left: 20px;">
                            <li><strong>Connection pooling:</strong> Reuse existing connections</li>
                            <li><strong>Keep-alive headers:</strong> Maintain persistent connections</li>
                            <li><strong>Load balancer:</strong> Distribute connections across servers</li>
                            <li><strong>Geographic proximity:</strong> Deploy closer to users</li>
                            <li><strong>Server capacity:</strong> Scale up connection limits</li>
                        </ul>
                    </div>
                </div>
                <div class="metric-description">
                    <h4>📤 http_req_sending</h4>
                    <p><strong>Request send time</strong> - Time to send request to server. Usually very low for GET requests.</p>
                    <div style="background: #f3e8ff; border-left: 4px solid #8b5cf6; padding: 15px; margin-top: 15px; border-radius: 6px;">
                        <h5 style="color: #7c3aed; margin-bottom: 8px;">💡 How to Improve:</h5>
                        <ul style="color: #6d28d9; font-size: 0.9rem; line-height: 1.6; padding-left: 20px;">
                            <li><strong>Network optimization:</strong> Use HTTP/2 for multiplexing</li>
                            <li><strong>Request size:</strong> Minimize headers and body content</li>
                            <li><strong>Connection reuse:</strong> Enable keep-alive connections</li>
                            <li><strong>Geographic location:</strong> Deploy servers closer to users</li>
                            <li><strong>Quality of Service:</strong> Ensure stable network connection</li>
                        </ul>
                    </div>
                </div>
                <div class="metric-description">
                    <h4>📥 http_req_receiving</h4>
                    <p><strong>Response download time</strong> - Time to download response body. High values suggest large responses or slow network.</p>
                    <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin-top: 15px; border-radius: 6px;">
                        <h5 style="color: #dc2626; margin-bottom: 8px;">💡 How to Improve:</h5>
                        <ul style="color: #b91c1c; font-size: 0.9rem; line-height: 1.6; padding-left: 20px;">
                            <li><strong>Enable compression:</strong> Use gzip/brotli for text responses</li>
                            <li><strong>Optimize bundle size:</strong> Remove unused code, tree shaking</li>
                            <li><strong>Image optimization:</strong> Compress images, use modern formats</li>
                            <li><strong>Streaming SSR:</strong> Send HTML chunks as they're ready</li>
                            <li><strong>Reduce payload:</strong> Send only necessary data to client</li>
                        </ul>
                    </div>
                </div>
                <div class="metric-description">
                    <h4>🚫 http_req_blocked</h4>
                    <p><strong>DNS lookup time</strong> - Time spent on DNS resolution and connection setup. Should be minimal after first request.</p>
                    <div style="background: #fff7ed; border-left: 4px solid #ea580c; padding: 15px; margin-top: 15px; border-radius: 6px;">
                        <h5 style="color: #c2410c; margin-bottom: 8px;">💡 How to Improve:</h5>
                        <ul style="color: #9a3412; font-size: 0.9rem; line-height: 1.6; padding-left: 20px;">
                            <li><strong>DNS optimization:</strong> Use fast DNS providers (Cloudflare, Google)</li>
                            <li><strong>DNS caching:</strong> Set appropriate TTL values</li>
                            <li><strong>Connection pooling:</strong> Reuse existing connections</li>
                            <li><strong>CDN usage:</strong> Reduce initial connection overhead</li>
                            <li><strong>Preconnect hints:</strong> Use rel="preconnect" for key domains</li>
                        </ul>
                    </div>
                </div>
                <div class="metric-description">
                    <h4>🔄 iteration_duration</h4>
                    <p><strong>Full test cycle time</strong> - Complete time for one user iteration including request + think time.</p>
                    <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; margin-top: 15px; border-radius: 6px;">
                        <h5 style="color: #047857; margin-bottom: 8px;">💡 How to Improve:</h5>
                        <ul style="color: #065f46; font-size: 0.9rem; line-height: 1.6; padding-left: 20px;">
                            <li><strong>Reduce overall latency:</strong> All above optimizations combined</li>
                            <li><strong>Parallel processing:</strong> Handle multiple requests concurrently</li>
                            <li><strong>User flow optimization:</strong> Minimize required interactions</li>
                            <li><strong>Progressive enhancement:</strong> Load critical content first</li>
                            <li><strong>Realistic think time:</strong> Adjust test parameters for real usage</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="chart-container">
                <h3 style="text-align: center; margin-bottom: 20px;">📊 Response Time Distribution</h3>
                <canvas id="metricsChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 40px; text-align: center;">
            <h2 style="color: #1e40af; margin-bottom: 20px;">🎯 Next Steps for Comparison</h2>
            <div style="max-width: 800px; margin: 0 auto; text-align: left;">
                <ol style="line-height: 2; font-size: 1.1rem;">
                    <li><strong>Run:</strong> <code style="background: #e5e7eb; padding: 4px 8px; border-radius: 4px;">k6 run k6.js</code></li>
                    <li><strong>Compare the HTML reports</strong> focusing on key metrics</li>
                    <li><strong>Look for differences</strong> in response times and error rates</li>
                </ol>
            </div>
        </div>

        <div class="footer">
            Generated by K6 • ${timestamp}
        </div>
    </div>

    <script>
        const ctx = document.getElementById('metricsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Average', 'Median', '90th %ile', '95th %ile', 'Maximum'],
                datasets: [{
                    label: 'Response Time (ms)',
                    data: [
                        ${formatMetricValue(httpReqDuration.avg) || 0},
                        ${formatMetricValue(httpReqDuration.med) || 0},
                        ${formatMetricValue(httpReqDuration['p(90)']) || 0},
                        ${formatMetricValue(httpReqDuration['p(95)']) || 0},
                        ${formatMetricValue(httpReqDuration.max) || 0}
                    ],
                    backgroundColor: [
                        '${avgGrade.color}40',
                        '#3b82f640',
                        '#f59e0b40',
                        '${p95Grade.color}40',
                        '#ef444440'
                    ],
                    borderColor: [
                        '${avgGrade.color}',
                        '#3b82f6',
                        '#f59e0b',
                        '${p95Grade.color}',
                        '#ef4444'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Response Time (ms)' }
                    }
                }
            }
        });
    </script>
</body>
</html>`;

    const summary = [
        '🚀 K6 Complete Test Summary',
        '▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔',
        `Total Requests: ${httpReqs.count || 0}`,
        `Error Rate: ${formatMetricValue(httpReqFailed.rate, '%')}`,
        `Avg Response: ${formatMetricValue(httpReqDuration.avg)}ms`,
        `95th Percentile: ${formatMetricValue(httpReqDuration['p(95)'])}ms`,
        `Peak Users: ${vusMax.max || 0}`,
        '',
        `✅ HTML Report: k6-report-${timestamp}.html`,
        `📊 JSON Data: k6-data-${timestamp}.json`,
    ].join('\n');

    const reportName = `k6-report-${timestamp}.html`;
    const jsonReportName = `k6-data-${timestamp}.json`;

    return {
        [reportName]: htmlReport,
        [jsonReportName]: JSON.stringify(jsonData, null, 2),
        stdout: summary,
    };
}

export function setup() {
    console.log('🚀 K6 Complete Test - One File, Complete Analysis');
    console.log(`📊 Testing: ${baseUrl}`);
    console.log('🎯 Generating comprehensive HTML report with all metrics explained');
    return {};
}

export function teardown(data) {
    console.log('✅ Test completed! Check your HTML report and JSON data files');
    console.log('📊 HTML Report: Visual analysis and insights');
    console.log('🔍 JSON Data: Raw metrics for further analysis');
}
