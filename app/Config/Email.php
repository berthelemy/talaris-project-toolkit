<?php

/**
 * File documentation for app/Config/Email.php.
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * SMTP and email delivery configuration loaded from environment.
 */
class Email extends BaseConfig
{
    public string $fromEmail  = 'noreply@example.com';
    public string $fromName   = 'Talaris Toolkit';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'smtp';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname
     */
    public string $SMTPHost = 'mailpit';

    /**
     * Which SMTP authentication method to use: login, plain
     */
    public string $SMTPAuthMethod = 'login';

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
    public int $SMTPPort = 1025;

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
    public string $SMTPCrypto = '';

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
    public string $mailType = 'text';

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

    /**
     * Hydrate email transport settings from environment overrides.
     */
    public function __construct()
    {
        parent::__construct();

        $this->fromEmail = (string) env('email.fromEmail', $this->fromEmail);
        $this->fromName = (string) env('email.fromName', $this->fromName);
        $this->recipients = (string) env('email.recipients', $this->recipients);
        $this->protocol = (string) env('email.protocol', $this->protocol);
        $this->mailPath = (string) env('email.mailPath', $this->mailPath);
        $this->SMTPHost = (string) env('email.SMTPHost', $this->SMTPHost);
        $this->SMTPAuthMethod = (string) env('email.SMTPAuthMethod', $this->SMTPAuthMethod);
        $this->SMTPUser = (string) env('email.SMTPUser', $this->SMTPUser);
        $this->SMTPPass = (string) env('email.SMTPPass', $this->SMTPPass);
        $this->SMTPPort = (int) env('email.SMTPPort', (string) $this->SMTPPort);
        $this->SMTPTimeout = (int) env('email.SMTPTimeout', (string) $this->SMTPTimeout);
        $this->SMTPKeepAlive = filter_var(env('email.SMTPKeepAlive', $this->SMTPKeepAlive), FILTER_VALIDATE_BOOL);
        $this->SMTPCrypto = (string) env('email.SMTPCrypto', $this->SMTPCrypto);
        $this->wordWrap = filter_var(env('email.wordWrap', $this->wordWrap), FILTER_VALIDATE_BOOL);
        $this->wrapChars = (int) env('email.wrapChars', (string) $this->wrapChars);
        $this->mailType = (string) env('email.mailType', $this->mailType);
        $this->charset = (string) env('email.charset', $this->charset);
        $this->validate = filter_var(env('email.validate', $this->validate), FILTER_VALIDATE_BOOL);
        $this->priority = (int) env('email.priority', (string) $this->priority);
        $this->CRLF = (string) env('email.CRLF', $this->CRLF);
        $this->newline = (string) env('email.newline', $this->newline);
        $this->BCCBatchMode = filter_var(env('email.BCCBatchMode', $this->BCCBatchMode), FILTER_VALIDATE_BOOL);
        $this->BCCBatchSize = (int) env('email.BCCBatchSize', (string) $this->BCCBatchSize);
        $this->DSN = filter_var(env('email.DSN', $this->DSN), FILTER_VALIDATE_BOOL);
    }
}
