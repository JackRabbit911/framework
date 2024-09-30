<?php declare(strict_types=1);

namespace Sys\Trait;

trait Ftp
{
    public function ftpConnect(): \FTP\Connection
    {
        $ftp = ftp_connect(env('FTP_HOST'));

        ftp_login($ftp, env('FTP_USER'), env('FTP_PASSWORD')) or 
        die('Failed to establish connection to ftp server: ' . env('FTP_HOST') . PHP_EOL);

        ftp_pasv($ftp, true);

        return $ftp;
    }

    public function fileExists(\Ftp\Connection $ftp, string $remote_file): bool
    {
        static $contents;

        $dir = dirname($remote_file);

        if (!isset($contents[$dir])) {
            $contents[$dir] = ftp_nlist($ftp, $dir);
        }

        return (!$contents[$dir]) ?: in_array($remote_file, $contents[$dir]);
    }
}
