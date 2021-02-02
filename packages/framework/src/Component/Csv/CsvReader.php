<?php

namespace Shopsys\FrameworkBundle\Component\Csv;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * @deprecated Class is obsolete and will be removed in the next major. Use SplFileObject::fgetcsv() instead.
 */
class CsvReader
{
    /**
     * @param string $filename
     * @param string $delimiter
     * @return array
     */
    public function getRowsFromCsv($filename, $delimiter = ';')
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use SplFileObject::fgetcsv() instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        if (!file_exists($filename) || !is_readable($filename)) {
            throw new FileNotFoundException();
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
