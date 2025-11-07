<?php
namespace App;

// Storing the data into storage folder
class Storage
{
    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = rtrim($dir, '/\\');
    }

    public function path(string $filename): string
    {
        return $this->dir . DIRECTORY_SEPARATOR . $filename;
    }

    public function writeJson(string $filename, array $data): void
    {
        $fullPath = $this->path($filename);
        $result = file_put_contents($fullPath, json_encode($data, JSON_PRETTY_PRINT));
        if ($result === false) {
            throw new \RuntimeException("Failed to write JSON file: $fullPath");
        }
    }

    public function writeCsv(string $filename, array $rows): void
    {
        $fullPath = $this->path($filename);
        $fp = fopen($fullPath, 'w');
        if (!$fp) {
            throw new \RuntimeException("Unable to open file for writing CSV: $fullPath");
        }
        if (empty($rows)) {
            fclose($fp);
            return;
        }

        // headers from keys of first row
        fputcsv($fp, array_keys($rows[0]));
        foreach ($rows as $row) {
            $transformedRow = [];

            // avoid writing 1 and 0 in csv
            foreach ($row as $value) {
                if (is_bool($value)) {
                    if ($value) {
                        $transformedRow[] = 'true';
                    } else {
                        $transformedRow[] = 'false';
                    }
                } else {
                    $transformedRow[] = $value;
                }
            }
            fputcsv($fp, $transformedRow);
        }
        fclose($fp);
    }
}
