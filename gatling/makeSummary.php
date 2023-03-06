<?php

declare(strict_types=1);

class PageStats
{
    private string $simulationDir;

    private string $name;

    private int $okRequestsCount = 0;

    private int $koRequestsCount = 0;

    private int $users;

    private bool $frozen = false;

    /**
     * @var int[]
     */
    private array $requestsTimes = [];

    public function __construct(string $simulationDir, string $name, int $users)
    {
        $this->simulationDir = $simulationDir;
        $this->name = $name;
        $this->users = $users;
    }

    public function freeze(): void
    {
        $this->frozen = true;
        asort($this->requestsTimes);
        $this->requestsTimes = array_values($this->requestsTimes);
    }

    public function addRequest(int $requestTime, bool $ok): void
    {
        if ($this->frozen) {
            throw new \Exception('Request can not be added, the PageStats is frozen');
        }
        $this->requestsTimes[] = $requestTime;
        $ok ? ++$this->okRequestsCount : ++$this->koRequestsCount;
    }

    public function getMinRequestTime(): int
    {
        $this->checkFrozen();

        return reset($this->requestsTimes);
    }

    public function getMaxRequestTime(): int
    {
        $this->checkFrozen();

        return end($this->requestsTimes);
    }

    public function getAvgRequestTime(): int
    {
        $this->checkFrozen();

        return (int)round(array_sum($this->requestsTimes) / count($this->requestsTimes));
    }

    public function getPercentileRequestTime(int $percentile): int
    {
        $this->checkFrozen();

        return $this->requestsTimes[(int)floor(count($this->requestsTimes) / 100 * $percentile)];
    }

    public function getSimulationDir(): string
    {
        return $this->simulationDir;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTotalIterations(): int
    {
        return count($this->requestsTimes);
    }

    public function getOkRequestsCount(): int
    {
        return $this->okRequestsCount;
    }

    public function getKoRequestsCount(): int
    {
        return $this->koRequestsCount;
    }

    public function getUsers(): int
    {
        return $this->users;
    }

    private function checkFrozen(): void
    {
        if (!$this->frozen) {
            throw new \Exception('Stats can not be returned, the PageStats has to be frozen');
        }
    }
}

class Summary
{
    /**
     * @var \PageStats[][]
     */
    private array $pagesStatsByUsersAndName = [];

    public function addRequest(
        string $simulationDir,
        string $name,
        int $users,
        int $requestStart,
        int $requestStop,
        bool $ok
    ): void {
        if (!isset($this->pagesStatsByUsersAndName[$users][$name])) {
            $this->pagesStatsByUsersAndName[$users][$name] = new PageStats($simulationDir, $name, $users);
        }

        $this->pagesStatsByUsersAndName[$users][$name]->addRequest($requestStop - $requestStart, $ok);
    }

    /**
     * @return \PageStats[]
     */
    public function getPagesStats(): array
    {
        $flattenPagesStats = array_merge(...array_map('array_values', $this->pagesStatsByUsersAndName));

        return array_map(static function (PageStats $pageStats) {
            $pageStats->freeze();

            return $pageStats;
        }, $flattenPagesStats);
    }
}

$summaryPath = '/gatlingResults/' . getenv('SUMMARY_DIR');
$simulationDirs = explode("\n", str_replace("\r", '', file_get_contents($summaryPath . '/results.log')));
$simulationDirs = array_filter($simulationDirs);

ob_start(function ($content) use ($summaryPath) {
    $file = fopen($summaryPath . '/index.html', 'wb');
    fwrite($file, $content);
    fclose($file);
});

$summary = new Summary();
foreach ($simulationDirs as $simulationDir) {
    $lines = explode(
        "\n",
        str_replace("\r", '', file_get_contents('/gatlingResults/' . $simulationDir . '/simulation.log'))
    );

    foreach ($lines as $line) {
        $data = explode("\t", $line);
        if (count($data) >= 6 && $data[0] === 'REQUEST') {
            $matches = null;
            if (preg_match('/^(?<type>[^_]+)__(?<users>\d+)__(?<name>.+)$/i', $data[3], $matches) !== false) {
                $summary->addRequest(
                    $simulationDir,
                    $matches['name'],
                    (int)$matches['users'],
                    (int)$data[4],
                    (int)$data[5],
                    $data[6] === 'OK',
                );
            }
        }
    }
}

$pagesStats = $summary->getPagesStats();

?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
              integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
              crossorigin="anonymous">
        <title>Gatling results</title>
    </head>
    <body>
    <div class="container">
        <h1>Gatling results <?php
            echo date('d.m.Y H:i:s') ?></h1>
        <a href="/">Show all results</a>
        <h2>Stress test</h2>
        <a href="../<?php
        echo trim(file_get_contents($summaryPath . '/stress.log')); ?>/index.html" target="_blank">[Here]</a>

        <h2>Page test</h2>
        <div class="row">
            <div class="col col-lg-2 col-md-12">
                <h3>#</h3>
                <table class="table table-striped table-bordered table-hover table-sm">
                    <thead>
                    <tr>
                        <th>Avg for copy</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($pagesStats as $pageStats) {
                        echo '
                            <tr>
                                <td>' . $pageStats->getAvgRequestTime() . '</td>                          
                            </tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col col-lg-10 col-md-12">
                <h3>Tested pages</h3>
                <table class="table table-striped table-bordered table-hover table-sm">
                    <thead>
                    <tr>
                        <th>Page</th>
                        <th>Users</th>
                        <th>Requests</th>
                        <th>Avg</th>
                        <th>OK</th>
                        <th>KO</th>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Percentile 50</th>
                        <th>Percentile 75</th>
                        <th>Percentile 95</th>
                        <th>Percentile 99</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($pagesStats as $pageStats) {
                        echo '
                            <tr>
                                <td><a href="../'
                            . $pageStats->getSimulationDir()
                            . '/index.html" target="_blank">'
                            . $pageStats->getName()
                            . '</a></td>
                                <td>'
                            . $pageStats->getUsers()
                            . '</td>
                                <td>'
                            . $pageStats->getTotalIterations()
                            . '</td>
                                <td>'
                            . $pageStats->getAvgRequestTime()
                            . '</td>
                                <td>'
                            . $pageStats->getOkRequestsCount()
                            . '</td>
                                <td'
                            . ($pageStats->getKoRequestsCount() > 0 ? ' class="bg-danger"' : '')
                            . '>'
                            . $pageStats->getKoRequestsCount()
                            . '</td>
                                <td>'
                            . $pageStats->getMinRequestTime()
                            . '</td>
                                <td>'
                            . $pageStats->getMaxRequestTime()
                            . '</td>
                                <td>'
                            . $pageStats->getPercentileRequestTime(50)
                            . '</td>
                                <td>'
                            . $pageStats->getPercentileRequestTime(75)
                            . '</td>
                                <td>'
                            . $pageStats->getPercentileRequestTime(95)
                            . '</td>
                                <td>'
                            . $pageStats->getPercentileRequestTime(99)
                            . '</td>
                            </tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </body>
    </html>
<?php

ob_end_flush();
