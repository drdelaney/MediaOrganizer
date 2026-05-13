<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = '';
    public string $fromName   = '';
    public string $recipients = '';

    public function __construct()
    {
        parent::__construct();

        // Load from database if not set by environment
        $configModel = new \App\Models\ConfigurationModel();
        
        $this->protocol = $configModel->getParam('email.protocol', $this->protocol);
        $this->SMTPHost = $configModel->getParam('email.SMTPHost', $this->SMTPHost);
        $this->SMTPUser = $configModel->getParam('email.SMTPUser', $this->SMTPUser);
        $this->SMTPPass = $configModel->getParam('email.SMTPPass', $this->SMTPPass);
        $this->SMTPPort = (int)$configModel->getParam('email.SMTPPort', $this->SMTPPort);
        $this->SMTPCrypto = $configModel->getParam('email.SMTPCrypto', $this->SMTPCrypto);
        $this->fromEmail = $configModel->getParam('email.fromEmail', $this->fromEmail);
        $this->fromName = $configModel->getParam('email.fromName', $this->fromName);
        $this->SMTPVerifyPeer = (bool)$configModel->getParam('email.SMTPVerifyPeer', $this->SMTPVerifyPeer);
        $this->SMTPVerifyPeerName = (bool)$configModel->getParam('email.SMTPVerifyPeerName', $this->SMTPVerifyPeerName);
    }

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'mail';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname
     */
    public string $SMTPHost = '';

    /**
     * SMTP Username
     */
    public string $SMTPUser = '';

    /**
     * SMTP Password
     */
    public string $SMTPPass = '';

    /**
     * SMTP Port
     */
    public int $SMTPPort = 25;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 5;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption.
     *
     * @var string '', 'tls' or 'ssl'. 'tls' will issue a STARTTLS command
     *             to the server. 'ssl' means implicit SSL. Connection on port
     *             465 should set this to ''.
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Verify SSL certificate peer.
     * Set to false to disable SSL certificate verification (not recommended for production).
     */
    public bool $SMTPVerifyPeer = true;

    /**
     * Verify SSL certificate peer name.
     * Set to false to disable SSL certificate name verification (not recommended for production).
     */
    public bool $SMTPVerifyPeerName = true;

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = 'html';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;
}
