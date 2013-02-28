<?php 

class SimpleFtp {

  private $conn;
  private $host;
  private $port;
  private $user;
  private $password;
  private $timeout = 5;
  private $connected = FALSE;
  private $transfer_mode = FTP_BINARY;

  /**
   *
   * @param type $host host string ftp.example.net/path1/path2
   * @param type $user username if any
   * @param type $password password if any
   */
  public function __construct ($host, $port = 21, $user = null, $password = null) {
    $this->host = $host;
    $this->user = $user;
    $this->port = $port;
    $this->password = $password;
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
