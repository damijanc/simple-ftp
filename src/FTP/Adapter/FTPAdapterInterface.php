<?php


namespace damijanc\FTP\Adapter;

interface FTPAdapterInterface
{
    public function ftp_connect($host, $port, $timeout);
    public function ftp_login($conn, $user, $password);
    public function ftp_quit($conn);
    public function ftp_pwd($conn);
    public function ftp_chdir($conn, $folder);
    public function ftp_nlist($conn, $path);
    public function ftp_rename($conn, $old_file, $new_file);
    public function ftp_delete($conn, $file);
    public function ftp_rmdir($conn, $folder);
    public function ftp_chmod($conn, $mode, $file);
    public function ftp_mkdir($conn, $folder);
    public function ftp_nb_fput($conn, $dir, $fp, $mode);
    public function ftp_nb_continue($conn);
    public function ftp_nb_fget($conn, $fp, $file, $mode);
    public function ftp_pasv($ftp_connection, $pasv);
}
