<?php

namespace damijanc\FTP\Adapter;

/**
 * Purpose of this class is to isolate library from native functions so that library is testable
 *
 * Class FTPAdapter
 * @package damijanc\FTP
 */
class FTPAdapter implements FTPAdapterInterface
{
    public function ftp_connect($host, $port, $timeout)
    {
        return ftp_connect($host, $port, $timeout);
    }

    public function ftp_login($conn, $user, $password)
    {
        return ftp_login($conn, $user, $password);
    }

    public function ftp_quit($conn)
    {
        return ftp__quit($conn);
    }

    public function ftp_pwd($conn)
    {
        return ftp_pwd($conn);
    }

    public function ftp_chdir($conn, $folder)
    {
        return ftp_chdir($conn, $folder);
    }

    public function ftp_nlist($conn, $path)
    {
        return ftp_nlist($conn, $path);
    }

    public function ftp_rename($conn, $old_file, $new_file)
    {
        return ftp_rename($conn, $old_file, $new_file);
    }

    public function ftp_delete($conn, $file)
    {
        return ftp_delete($conn, $file);
    }

    public function ftp_rmdir($conn, $folder)
    {
        return ftp_rmdir($conn, $folder);
    }

    public function ftp_chmod($conn, $mode, $file)
    {
        return ftp_chmod($conn, $mode, $file);
    }

    public function ftp_mkdir($conn, $folder)
    {
        return ftp_mkdir($conn, $folder);
    }

    public function ftp_nb_fput($conn, $dir, $fp, $mode)
    {
        return ftp_nb_fput($conn, $dir, $fp, $mode);
    }

    public function ftp_nb_continue($conn)
    {
        return ftp_nb_continue($conn);
    }

    public function ftp_nb_fget($conn, $fp, $file, $mode)
    {
        return ftp_nb_fget($conn, $fp, $file, $mode);
    }

    public function ftp_pasv($ftp_connection, $pasv)
    {
        ftp_pasv($ftp_connection, $pasv);
    }
}
