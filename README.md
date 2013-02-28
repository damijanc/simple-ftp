simple-ftp
==========

Simple wrapper for PHP (http://php.net/manual/en/book.ftp.php) FTP

Motivation for this class was to simplify usage of FTP
so instead of doing:

$conn = ftp_connect($host, $port, $timeout);
if ($conn) {
  $login_result = ftp_login($conn, $user, $password);
  if ($login_result) {
          $connected = TRUE;
  }
}

We simply do 

$ftp = new SimpleFtp($host, $port, $user, $password);
$ftp->connect();


In addition we can use shell commands like:

cd  -change dir
put -upload a file
ls - list directory
get - download file


Example:

$file = '/tmp/myfile.zip';

//connect to server
$ftp = new SimpleFtp('ftp.example.com', $port = 21, 'user', 'pass');
$ftp->connect();
//got to folder
$ftp->cd('Folder1');
//upload file
$ftp->put($file);
//list content
$ftp->ls();
//end session
$ftp->disconnect();

TODO:

- add delete support
- add multiple file/folder upload
- add upload/download progress display
...
