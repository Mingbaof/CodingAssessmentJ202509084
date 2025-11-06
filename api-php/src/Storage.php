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
        file_put_contents($this->path($filename), json_encode($data, JSON_PRETTY_PRINT));
    }

    public function writeCsv(string $filename, array $rows): void
    {
        $fp = fopen($this->path($filename), 'w');
        if (!$fp) throw new \RuntimeException('Unable to open file for writing CSV');
        if (empty($rows)) { fclose($fp); return; }
        // headers from keys of first row
        fputcsv($fp, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($fp, array_map(fn($v) => is_bool($v) ? ($v ? 'true':'false') : $v, $row));
        }
        fclose($fp);
    }
}
