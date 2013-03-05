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
class SimpleFtp {

  private $conn;
  private $host;
  private $port = 21;
  private $user;
  private $password;
  private $timeout = 5;
  private $connected = FALSE;
  private $transfer_mode = FTP_BINARY;

  /**
   * Constructor
   */
  public function __construct ($options) {
    if (is_array($options)) {
      if (array_key_exists('server', $options))
        $this->host = $options['server'];
      if (array_key_exists('user', $options))
        $this->user = $options['user'];
      if (array_key_exists('port', $options))
        $this->port = $options['port'];
      if (array_key_exists('pass', $options))
        $this->password = $options['pass'];
    }
  }

  public function set_timeout ($t = 5) {
    if (is_numeric($t) && $t >= 1)
      $this->timeout = floor($t);
  }

  private function check_variables () {
    if (empty($this->host)) {
      throw new Exception('Host not set !!!');
    }
    if (empty($this->user)) {
      throw new Exception('User not set !!!');
    }
    if (!is_numeric($this->port)) {
      throw new Exception('Port not set !!!');
    }

    if (empty($this->password)) {
      throw new Exception('Password not set !!!');
    }
  }

  public function transfer_mode ($m = FTP_BINARY) {
    $this->transfer_mode = $m;
  }

  public function connect () {

    $this->check_variables();

    if ($this->connected == FALSE) {

      $this->conn = ftp_connect($this->host, $this->port, $this->timeout);

      if ($this->conn) {
        // Open a session to an external ftp site
        $login_result = ftp_login($this->conn, $this->user, $this->password);
        if ($login_result) {
          $this->connected = TRUE;
          return;
        }
      }
    }

    $this->connected = FALSE;
    throw new Exception('Failed to connect');
  }

  function ftp_parse_response ($response, &$errstr) {
    if (!is_array($response)) {
      $errstr = 'Parameter \$response must be an array';
      return false;
    }

    foreach ($response as $r) {
      $code = substr(trim($r), 0, 3);

      if (!is_numeric($code)) {
        $errstr = "$code is not a valid FTP code";
      }

      if ($code > 400) {
        $errstr = $r;
        return false;
      }
    }

    return true;
  }

  public function disconnect () {
    ftp_quit($this->conn);
    $this->connected = FALSE;
  }

  public function cd ($folder) {
    if (!$this->connected)
      throw new Exception('You are not connected');

    if (ftp_pwd($this->conn) != $folder) {
      if (ftp_chdir($this->conn, $folder) != FALSE) {
        return TRUE;
      }
    }

    return FALSE;
  }

  public function pwd () {
    if (!$this->connected)
      throw new Exception('You are not connected');
    $s = ftp_pwd($this->conn);
    echo $s ? "$s\n" : "Can't query folder. \n";
  }

  public function ls () {
    if (!$this->connected)
      throw new Exception('You are not connected');

    if ($arr = ftp_nlist($this->conn, '.')) {
      foreach ($arr as $file) {
        echo $file . "\n";
      }
    }
  }

  public function mv ($old_file, $new_file) {
    if (!$this->connected)
      throw new Exception('You are not connected');
    if (ftp_rename($this->conn, $old_file, $new_file)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  public function rm ($file) {
    if (!$this->connected)
      throw new Exception('You are not connected');
    if (ftp_delete($this->conn, $file)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  public function rmdir ($folder) {
    if (!$this->connected)
      throw new Exception('You are not connected');
    if (ftp_rmdir($this->conn, $folder)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  public function chmod ($mode,$file) {
    if (!$this->connected)
      throw new Exception('You are not connected');

    if (ftp_chmod($this->conn, $mode, $file) !== false) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
  public function mkdir ($folder) {
    if (!$this->connected)
      throw new Exception('You are not connected');
    ftp_mkdir($this->conn, $folder);
  }

  public function put ($file) {


    if (!$this->connected)
      throw new Exception('You are not connected');
    //open file pointer
    $fp = fopen($file, 'r');

    if ($fp === FALSE)
      throw new Exception('file does not exist');

    //get filename from path

    $path_parts = pathinfo($file);

    $ret = ftp_nb_fput($this->conn, $path_parts['basename'] , $fp, FTP_BINARY);
    while ($ret == FTP_MOREDATA) {

      // We could print some progress bar since we are not blocking
      // Continue downloading...
      $ret = ftp_nb_continue($this->conn);
    }
    if ($ret != FTP_FINISHED) {
      echo "There was an error downloading the file...";
      exit(1);
    }

    // close filepointer
    fclose($fp);
  }

  public function get ($file, $remote_location) {
    if (!$this->connected)
      throw new Exception('You are not connected');
    //open file pointer
    $fp = fopen($file, 'w');

    $ret = ftp_nb_fget($this->conn, $fp, $file, FTP_BINARY);
    while ($ret == FTP_MOREDATA) {

      // We could print some progress bar since we are not blocking
      // Continue downloading...
      $ret = ftp_nb_continue($this->conn);
    }
    if ($ret != FTP_FINISHED) {
      echo "There was an error downloading the file...";
      exit(1);
    }

    // close filepointer
    fclose($fp);
  }

}

?>
