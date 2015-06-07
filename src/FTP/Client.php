<?php
/*
   Copyright 2013 Damijan Cavar

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
namespace damijanc\FTP;

use damijanc\FTP\Adapter\FTPAdapterInterface;

class Client
{

    private $conn;
    private $host;
    private $port = 21;
    private $user;
    private $password;
    private $timeout = 5;
    private $connected = false;
    private $transfer_mode = FTP_BINARY;
    private $passive = true;

    /**
     * @var FTPAdapterInterface
     */
    private $adapter;

    /**
     * Constructor
     */
    public function __construct(array $options, FTPAdapterInterface $adapter = null)
    {
        if (is_array($options)) {
            if (array_key_exists('server', $options)) {
                $this->host = $options['server'];
            }
            if (array_key_exists('user', $options)) {
                $this->user = $options['user'];
            }
            if (array_key_exists('port', $options)) {
                $this->port = $options['port'];
            }
            if (array_key_exists('pass', $options)) {
                $this->password = $options['pass'];
            }
            if (array_key_exists('passive', $options)) {
                $this->passive = $options['passive'];
            }
        }

        if ($adapter == null) {
            $this->adapter = new Adapter\FTPAdapter();
        } else {
            $this->adapter = $adapter;
        }
    }

    public function set_timeout($t = 5)
    {
        if (is_numeric($t) && $t >= 1) {
            $this->timeout = floor($t);
        }
    }

    private function check_variables()
    {
        if (empty($this->host)) {
            throw new \Exception('Host not set !!!');
        }
        if (empty($this->user)) {
            throw new \Exception('User not set !!!');
        }
        if (!is_numeric($this->port)) {
            throw new \Exception('Port not set !!!');
        }

        if (empty($this->password)) {
            throw new \Exception('Password not set !!!');
        }
    }

    public function transfer_mode($m = FTP_BINARY)
    {
        $this->transfer_mode = $m;
    }

    public function connect()
    {
        $this->check_variables();

        if ($this->connected == false) {

            $this->conn = $this->adapter->ftp_connect($this->host, $this->port, $this->timeout);

            if ($this->conn) {

                // Open a session to an external ftp site
                $login_result = $this->adapter->ftp_login($this->conn, $this->user, $this->password);
                if ($login_result) {

                    if ($this->passive) {
                        $this->adapter->ftp_pasv($this->conn, $this->passive);
                    }
                    
                    $this->connected = true;
                    return true;
                }
            }
        }

        $this->connected = false;
        throw new \Exception('Failed to connect');
    }

    public function disconnect()
    {
        $this->adapter->ftp_quit($this->conn);
        $this->connected = false;
    }

    public function cd($folder)
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }

        if (ftp_pwd($this->conn) != $folder) {
            if (ftp_chdir($this->conn, $folder) != false) {
                return true;
            }
        }

        throw new \Exception('Unable to cd to folder.');
    }

    public function pwd()
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }
        $s = $this->adapter->ftp_pwd($this->conn);
        return $s ? "$s\n" : "Can't query folder. \n";
    }

    public function ls()
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }

        if ($arr = $this->adapter->ftp_nlist($this->conn, '.')) {
          return $arr;
        }
    }

    public function mv($old_file, $new_file)
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }
        if (ftp_rename($this->conn, $old_file, $new_file)) {
            return true;
        } else {
            throw new \Exception('Unable to mv.');
        }
    }

    public function rm($file)
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }
        if (ftp_delete($this->conn, $file)) {
            return true;
        } else {
            throw new \Exception('Unable to rm.');
        }
    }

    public function rmdir($folder)
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }
        if (ftp_rmdir($this->conn, $folder)) {
            return true;
        } else {
            throw new \Exception('Unable to rmdir.');
        }
    }

    public function chmod($mode, $file)
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }

        if (ftp_chmod($this->conn, $mode, $file) !== false) {
            return true;
        } else {
            throw new \Exception('Unable to change chmod .');
        }
    }

    public function mkdir($folder)
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }

        $this->adapter->ftp_mkdir($this->conn, $folder);
    }

    public function put($file)
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }
        //open file pointer
        $fp = fopen($file, 'r');

        if ($fp === false) {
            throw new \Exception('file does not exist');
        }

        //get filename from path

        $path_parts = pathinfo($file);

        $ret = $this->adapter->ftp_nb_fput($this->conn, $path_parts['basename'], $fp, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {

            // We could print some progress bar since we are not blocking
            // Continue downloading...
            $ret = $this->adapter->ftp_nb_continue($this->conn);
        }
        if ($ret != FTP_FINISHED) {
            throw new \Exception('There was an error uploading file...');
        }

        // close filepointer
        fclose($fp);

        return true;
    }

    public function get($file, $remote_location)
    {
        if (!$this->connected) {
            throw new \Exception('You are not connected');
        }
        //open file pointer
        $fp = fopen($file, 'w');

        $ret = $this->adapter->ftp_nb_fget($this->conn, $fp, $file, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {

            // We could print some progress bar since we are not blocking
            // Continue downloading...
            $ret = $this->adapter->ftp_nb_continue($this->conn);
        }
        if ($ret != FTP_FINISHED) {
            throw new \Exception("There was an error downloading the file...");
        }

        // close filepointer
        fclose($fp);

        return true;
    }

}

?>
