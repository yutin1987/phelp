<?php
namespace Phelp;

/**
 * Simple FTP Class
 * 
 * @category Simple
 * @package  FTP
 * Easy-to-use library for FTP
 */
final class FTP
{
    /**
     * FTP host
     *
     * @var string $_host
     */
    private $_host;

    /**
     * FTP port
     *
     * @var int $_port
     */
    private $_port = 21;

    /**
     * FTP user
     *
     * @var string $_user
     */
    private $_user;

    /**
     * FTP password
     *
     * @var string $_pwd
     */
    private $_pwd;
    
    /**
     * FTP stream
     *
     * @var resource $_id
     */
    private $_stream = false;

    /**
     * FTP timeout
     *
     * @var int $_timeout
     */
    private $_timeout = 90;

    /**
     * Last error
     *
     * @var string $error
     */
    public $error;

    /**
     * FTP passive mode flag
     *
     * @var bool $passive
     */
    public $passive = true;

    /**
     * SSL-FTP connection flag
     *
     * @var bool $ssl
     */
    public $ssl = false;

    /**
     * Default path
     *
     * @var string $path
     */
    public $defaultPath = null;

    /**
     * System type of FTP server
     *
     * @var string $systemType
     */
    public $systemType;

    /**
     * Initialize connection params
     *
     * @param string $uri     伺服器(sftp://user:pwd@host:port)
     * @param int    $timeout Timeout
     *
     * @return void
     * @throws Exception
     */
    public function __construct($uri = null, $timeout = 90)
    {
        $uri = parse_url($uri);

        $this->_host = $uri['host'];
        $this->_user = $uri['user'];
        $this->_pwd  = $uri['pass'];

        if (!extension_loaded('ftp')) {
            throw new Exception('PHP extension FTP is not loaded.');
        }

        if ('sftp' === $uri['scheme']) {
            $this->ssl = true;
        } elseif ('ftp' === $uri['scheme'] || empty($uri['scheme'])) {
            $this->ssl = false;
        } else {
            throw new Exception('Invalid uri, scheme must be ftp or sftp');
        }

        if (0 < $uri['port']) {
            $this->_port = intval($uri['port'], 10);
        }

        if (isset($uri['path'])) {
            $this->defaultPath = $uri['path'];
        }
    }

    /**
     * Auto close connection
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Connect to FTP server
     *
     * @return bool
     */
    public function connect()
    {
        if ($this->ssl) {
            $this->_stream = ftp_ssl_connect(
                $this->_host,
                $this->_port,
                $this->_timeout
            );
        } else {
            $this->_stream = ftp_connect(
                $this->_host,
                $this->_port,
                $this->_timeout
            );
        }

        if (false == $this->_stream) {
            if (false === $this->ssl) {
                $this->error = "Failed to connect to {$this->_host}";
            } elseif (true === $this->ssl) {
                $this->error = "Failed to connect to {$this->_host} (SSL connection)";
            } else {
                $this->error = "Failed to connect to {$this->_host} (invalid connection type)";
            }
        }

        return ($this->_stream) ? $this->login() : false;
    }

    /**
     * Login to FTP
     * 
     * @return bool
     */
    public function login()
    {
        $reply = ftp_login($this->_stream, $this->_user, $this->_pwd);

        if ($reply) {
            ftp_pasv($this->_stream, (bool) $this->passive);

            $this->systemType = ftp_systype($this->_stream);

            if ($this->defaultPath) {
                $this->cd($this->defaultPath);
            }
        } else {
            $this->error = "Failed to connect to {$this->_host} (login failed)";
        }

        return ($reply) ? true : false;
    }

    /**
     * Close FTP connection
     * 
     * @return void
     */
    public function close()
    {
        if ($this->_stream) {
            ftp_close($this->_stream);

            $this->_stream = false;
        }
    }

    /**
     * Change currect folder on FTP server
     *
     * @param string $folder 資料夾
     * 
     * @return bool
     */
    public function cd($folder)
    {
        $reply = ftp_chdir($this->_stream, $folder);

        if (!$reply) {
            $this->error = "Failed to change folder to \"{$folder}\"";
        }

        return ($reply) ? true : false;
    }

    /**
     * Set file permissions
     *
     * @param int    $permissions 權限
     * @param string $remote_file 遠端檔案
     * 
     * @return false
     */
    public function chmod($permissions, $remote_file = null)
    {
        $reply = ftp_chmod($this->_stream, $permissions, $remote_file);

        if (!$reply) {
            $this->error = "Failed to set file permissions for \"{$remote_file}\"";
        }

        return ($reply) ? true : false;
    }

    /**
     * Delete file on FTP server
     *
     * @param string $remote_file 遠端檔案
     * 
     * @return bool
     */
    public function delete($remote_file = null)
    {
        $reply = ftp_delete($this->_stream, $remote_file);

        if (!$reply) {
            $this->error = "Failed to delete file \"{$remote_file}\"";
        }

        return ($reply) ? true : false;
    }

    /**
     * Download file from server
     *
     * @param string $remote_file 本地檔案
     * @param string $local_file  遠端檔案
     * @param int    $mode        模式
     * 
     * @return bool
     */
    public function get($remote_file = null, $local_file = null, $mode = FTP_ASCII)
    {
        $reply = ftp_get($this->_stream, $local_file, $remote_file, $mode);

        if (!$reply) {
            $this->error = "Failed to download file \"{$remote_file}\"";
        }

        return ($reply) ? true : false;
    }

    /**
     * Get list of files/directories in directory
     *
     * @param string $directory 遠端路徑
     * 
     * @return array
     */
    public function ls($directory = null)
    {
        $reply = ftp_nlist($this->_stream, $directory);

        if (!$reply) {
            $this->error = "Failed to get directory list";
        }

        return ((bool) $reply) ? $reply : [];
    }

    /**
     * Create directory on FTP server
     *
     * @param string $directory 遠端路徑
     * 
     * @return bool
     */
    public function mkdir($directory = null)
    {
        $reply = ftp_mkdir($this->_stream, $directory);

        if (!$reply) {
            $this->error = "Failed to create directory \"{$directory}\"";
        }

        return ($reply) ? true : false;
    }
     
    /**
     * Upload file to server
     *
     * @param string $local_file  本地路徑
     * @param string $remote_file 遠端路徑
     * @param int    $mode        模式
     * 
     * @return bool
     */
    public function put($local_file = null, $remote_file = null, $mode = FTP_ASCII)
    {
        $reply = ftp_put($this->_stream, $remote_file, $local_file, $mode);

        if (!$reply) {
            $this->error = "Failed to upload file \"{$local_file}\"";
        }

        return ($reply) ? true : false;
    }

    /**
     * Get current directory
     *
     * @return string
     */
    public function pwd()
    {
        return ftp_pwd($this->_stream);
    }
    
    /**
     * Rename file on FTP server
     *
     * @param string $old_name 遠端檔案
     * @param string $new_name 新檔名
     * 
     * @return bool
     */
    public function rename($old_name = null, $new_name = null)
    {
        $reply = ftp_rename($this->_stream, $old_name, $new_name);

        if (!$reply) {
            $this->error = "Failed to rename file \"{$old_name}\"";
        }

        return ($reply) ? true : false;
    }

    /**
     * Remove directory on FTP server
     *
     * @param string $directory 遠端路徑
     * 
     * @return bool
     */
    public function rmdir($directory = null)
    {
        $reply = ftp_rmdir($this->_stream, $directory);

        if (!$reply) {
            $this->error = "Failed to remove directory \"{$directory}\"";
        }

        return ($reply) ? true : false;
    }
}
?>