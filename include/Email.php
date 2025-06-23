<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer; 
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as Message;

/**
 * E-mail Provider Abstraction
 *
 * @since 1.10.00
 */
class Email
{
    private array $mailConnections = [];

    public function __construct(
        private Variables $vars,
    ) {
        // Property promotion
    }

    /**
     * Retrieve the mailer details from settings or config.
     *
     * @since 1.10.00
     */
    public function getSettings(): array
    {
        $settings = [];
        if (empty($this->vars->mailer)) {
            $settings['type'] = $this->vars->settings['mailer_type'] ?? '';
            $settings['host'] = $this->vars->settings['mailer_host'] ?? '';
            $settings['port'] = $this->vars->settings['mailer_port'] ?? '';
            $settings['username'] = $this->vars->settings['mailer_username'] ?? '';
            $settings['password'] = $this->vars->settings['mailer_password'] ?? '';
            $settings['tls'] = $this->vars->settings['mailer_tls'] ?? '';
        } else {
            $settings['type'] = $this->vars->mailer['type'] ?? '';
            $settings['host'] = $this->vars->mailer['host'] ?? '';
            $settings['port'] = $this->vars->mailer['port'] ?? '';
            $settings['username'] = $this->vars->mailer['username'] ?? '';
            $settings['password'] = $this->vars->mailer['password'] ?? '';
            $settings['tls'] = $this->vars->mailer['tls'] ?? '';

            $settings['type'] = (string) $settings['type'];
            $settings['host'] = (string) $settings['host'];
            $settings['username'] = (string) $settings['username'];
            $settings['password'] = (string) $settings['password'];
            $settings['tls'] = (string) $settings['tls'];
        }
        $settings['port'] = (int) $settings['port'];
        switch ($settings['type']) {
            case 'socket_SMTP':
                // TODO: Force this to symfony after removal.
                break;
            case 'symfony':
                $settings['type'] = 'symfony';
                break;
            default:
                $settings['type'] = 'default';
        }
        switch ($settings['tls']) {
            case 'off':
            case 'on':
                // These are the allowed changes.
                break;
            default:
                $settings['tls'] = 'auto';
        }
        return $settings;
    }

    /**
     * Send a mail message.
     *
     * Works just like php's mail() function, but allows sending trough alternative mailers as well.
     *
     * @since 1.9.2
     * @return bool Success
     */
    private function altMail(Address $to, string $subject, string $message, string $additional_headers, Address $from, bool $messageIsHTML, string $charset): bool
    {
        $mailer = $this->getSettings();

        $message = str_replace(["\r\n", "\r", "\n"], ["\n", "\n", "\r\n"], $message);
        $subject = str_replace(["\r", "\n"], ['', ''], $subject);

        if ($messageIsHTML) {
            $content_type = 'text/html';
        } else {
            $content_type = 'text/plain';
        }

        switch ($mailer['type']) {
            case 'socket_SMTP':
                require_once(ROOT . 'include/smtp.inc.php');

                if (! isset($this->mailConnections['socket_SMTP'])) {
                    if ($this->vars->debug) {
                        $mail = new socket_SMTP(true, ROOT . 'smtp-log.txt');
                    } else {
                        $mail = new socket_SMTP();
                    }
                    $this->mailConnections['socket_SMTP'] = $mail;
                    if (! $mail->connect($mailer['host'], $mailer['port'], $mailer['username'], $mailer['password'])) {
                        return false;
                    }
                    register_shutdown_function(array($mail, 'disconnect'));
                } else {
                    $mail = $this->mailConnections['socket_SMTP'];
                    if (false === $mail->connection) {
                        return false;
                    }
                }

                $additional_headers = explode("\r\n", $additional_headers);
                $additional_headers[] = "Content-Type: $content_type; charset=$charset";
                $additional_headers[] = "Subject: $subject";
                $additional_headers[] = 'To: ' . $to->toString();
                $additional_headers[] = $this->smtpHeaderFrom($from->getName(), $from->getAddress());
                $additional_headers = implode("\r\n", $additional_headers);

                return $mail->sendMessage($from->getAddress(), $to, $message, $additional_headers);

            case 'symfony':
                if (! isset($this->mailConnections['symfony'])) {
                    if ($mailer['username'] !== '') {
                        $password = $mailer['password'];
                        if ($password !== '') {
                            $password = ':' . urlencode($password);
                        }
                        $login = urlencode($mailer['username']) . $password . '@';
                    } else {
                        $login = '';
                    }
                    $host = urlencode($mailer['host']) . ':' . (string) $mailer['port'];
                    switch ($mailer['tls']) {
                        case 'off':
                            $options = '?auto_tls=false';
                            break;
                        case 'on':
                            $options = '?require_tls=true';
                            break;
                        default:
                            $options = '';
                    }
                    $transport = Transport::fromDsn("smtp://$login$host$options");
                    $mail = new Mailer($transport);

                    $this->mailConnections['symfony'] = $mail;
                } else {
                    $mail = $this->mailConnections['symfony'];
                }

                $email = new Message();
                $email->from($from);
                $email->to($to);
                $email->subject($subject);
                if ($messageIsHTML) {
                    $email->html($message, $charset);
                } else {
                    $email->text($message, $charset);
                }

                try {
                    $mail->send($email);
                } catch (TransportExceptionInterface $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                    return false;
                }
                return true;

            default:
                $additional_headers .= 
                    "\r\n" . $this->smtpHeaderFrom($from->getName(), $from->getAddress()) .
                    "\r\nContent-Type: $content_type; charset=$charset";
                if (ini_get('safe_mode') == "1") {
                    $return = mail($to->toString(), $subject, $message, $additional_headers);
                } else {
                    $params = '-f ' . $from->getAddress();
                    $return = mail($to->toString(), $subject, $message, $additional_headers, $params);
                }
                if (! $return) {
                    trigger_error($this->vars->lang['emailErrorPhp'], E_USER_WARNING);
                }
                return $return;
        }
    }

    /**
     * Send email with default headers.
     *
     * @since 1.9.11.15 formerly xmb_mail()
     * @since 1.10.00
     * @param string $to      Pass through to altMail()
     * @param string $subject Pass through to altMail()
     * @param string $message Pass through to altMail()
     * @param string $charset The character set used in $message param.
     * @param bool   $html    Optional. Set to true if the $message param is HTML formatted.
     * @return bool
     */
    public function send(string $to, string $subject, string $message, string $charset, bool $html = false): bool
    {
        $rawbbname = rawHTML($this->vars->settings['bbname']);
        $rawusername = rawHTML($this->vars->self['username'] ?? '');
        $rawfrom = rawHTML($this->vars->settings['adminemail']);

        if (PHP_OS_FAMILY == 'Windows') {  // Official XMB hack for PHP bug #45305 a.k.a. #28038
            ini_set('sendmail_from', $rawfrom);
        }

        $from = new Address($rawfrom, $rawbbname);
        $toAddress = new Address($to);

        $headers = [
            'X-Mailer: PHP',
            'X-AntiAbuse: Board servername - ' . $this->vars->cookiedomain,
        ];
        if ($rawusername != '') {
            $headers[] = "X-AntiAbuse: Username - $rawusername";
        }
        $headers = implode("\r\n", $headers);

        return $this->altMail($toAddress, $subject, $message, $headers, $from, $html, $charset);
    }

    /**
     * Simple SMTP message From header formation.
     *
     * @since 1.9.11.08
     * @param string $fromname Will be converted to an SMTP quoted string.
     * @param string $fromaddress Must be a fully validated e-mail address.
     * @return string
     */
    private function smtpHeaderFrom(string $fromname, string $fromaddress): string
    {
        $fromname = preg_replace('@([^\\t !\\x23-\\x5b\\x5d-\\x7e])@', '\\\\$1', $fromname);
        return 'From: "'.$fromname.'" <'.$fromaddress.'>';
    }
}
