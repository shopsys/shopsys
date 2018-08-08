<?php

namespace Shopsys\FrameworkBundle\Component\Csv;

class CsvReader
{
    public function getRowsFromCsv(string $filename, string $delimiter = ';'): array
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \Symfony\Component\Filesystem\Exception\FileNotFoundException();
        }

        $rows = [];

        $handle = fopen($filename, 'r');
        if ($handle === false) {
            return $rows;
        }

        do {
            $row = fgetcsv($handle, 0, $delimiter);

            if ($row === false) {
                break;
            }

            $rows[] = $row;
        } while (true);

        fclose($handle);

        return $rows;
    }
}
