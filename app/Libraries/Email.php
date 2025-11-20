<?php

namespace App\Libraries;

use CodeIgniter\Email\Email as BaseEmail;

class Email extends BaseEmail
{
    /**
     * Connect to SMTP with SSL verification options
     *
     * @return bool
     */
    protected function SMTPConnect()
    {
        if ($this->isSMTPConnected()) {
            return true;
        }

        $ssl = '';

        // Connection to port 465 should use implicit TLS (without STARTTLS)
        // as per RFC 8314.
        if ($this->SMTPPort === 465) {
            $ssl = 'tls://';
        }
        // But if $SMTPCrypto is set to `ssl`, SSL can be used.
        if ($this->SMTPCrypto === 'ssl') {
            $ssl = 'ssl://';
        }

        // Log connection attempt details for debugging
        $connectionString = $ssl . $this->SMTPHost . ':' . $this->SMTPPort;
        log_message('info', "Attempting SMTP connection to: {$connectionString}");
        log_message('info', "SMTP Crypto: {$this->SMTPCrypto}, Timeout: {$this->SMTPTimeout}s");
        log_message('info', "SSL Verify Peer: " . ($this->SMTPVerifyPeer ?? true ? 'true' : 'false'));
        log_message('info', "SSL Verify Peer Name: " . ($this->SMTPVerifyPeerName ?? true ? 'true' : 'false'));

        // Create stream context with SSL verification options
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => $this->SMTPVerifyPeer ?? true,
                'verify_peer_name' => $this->SMTPVerifyPeerName ?? true,
                'allow_self_signed' => !($this->SMTPVerifyPeer ?? true),
            ]
        ]);

        $this->SMTPConnect = stream_socket_client(
            $connectionString,
            $errno,
            $errstr,
            $this->SMTPTimeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (! $this->isSMTPConnected()) {
            $errorDetails = "Connection failed to {$connectionString} - Error #{$errno}: {$errstr}";
            log_message('error', "SMTP Connection Error: {$errorDetails}");
            $this->setErrorMessage(lang('Email.SMTPError', [$errno . ' ' . $errstr]));

            return false;
        }

        log_message('info', "SMTP connection established successfully");
        stream_set_timeout($this->SMTPConnect, $this->SMTPTimeout);
        
        $smtpResponse = $this->getSMTPData();
        log_message('info', "SMTP initial response: {$smtpResponse}");
        $this->setErrorMessage($smtpResponse);

        if ($this->SMTPCrypto === 'tls') {
            log_message('info', "Starting TLS encryption...");
            $this->sendCommand('hello');
            $this->sendCommand('starttls');
            
            // Set stream context options before enabling crypto
            stream_context_set_option($this->SMTPConnect, 'ssl', 'verify_peer', $this->SMTPVerifyPeer ?? true);
            stream_context_set_option($this->SMTPConnect, 'ssl', 'verify_peer_name', $this->SMTPVerifyPeerName ?? true);
            stream_context_set_option($this->SMTPConnect, 'ssl', 'allow_self_signed', !($this->SMTPVerifyPeer ?? true));
            
            $crypto = stream_socket_enable_crypto(
                $this->SMTPConnect,
                true,
                STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT
                | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT
                | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
            );

            if ($crypto !== true) {
                $cryptoError = $this->getSMTPData();
                log_message('error', "TLS encryption failed: {$cryptoError}");
                $this->setErrorMessage(lang('Email.SMTPError', [$cryptoError]));

                return false;
            }
            log_message('info', "TLS encryption established successfully");
        }

        return $this->sendCommand('hello');
    }

    /**
     * Get SMTP server response data with UTF-8 encoding validation
     *
     * @return string
     */
    protected function getSMTPData()
    {
        $data = '';

        while ($str = fgets($this->SMTPConnect, 512)) {
            $data .= $str;

            if ($str[3] === ' ') {
                break;
            }
        }

        // Ensure the data is valid UTF-8 to prevent MessageFormatter errors
        // Replace invalid UTF-8 sequences with replacement character
        if (!mb_check_encoding($data, 'UTF-8')) {
            $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }

        return $data;
    }
}
