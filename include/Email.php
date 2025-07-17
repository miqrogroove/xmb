<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email as Message;
use Symfony\Component\Mime\Header\Headers;

/**
 * E-mail Provider Abstraction
 *
 * @since 1.10.00
 */
class Email
{
    private MailerInterface $mailer;

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
            $settings['port'] = intval($this->vars->settings['mailer_port'] ?? '');
            $settings['username'] = $this->vars->settings['mailer_username'] ?? '';
            $settings['password'] = $this->vars->settings['mailer_password'] ?? '';
            $settings['tls'] = $this->vars->settings['mailer_tls'] ?? '';
            $settings['dkim_key_path'] = $this->vars->settings['mailer_dkim_key_path'] ?? '';
            $settings['dkim_domain'] = $this->vars->settings['mailer_dkim_domain'] ?? '';
            $settings['dkim_selector'] = $this->vars->settings['mailer_dkim_selector'] ?? '';
        } else {
            $settings['type'] = strval($this->vars->mailer['type'] ?? '');
            $settings['host'] = strval($this->vars->mailer['host'] ?? '');
            $settings['port'] = intval($this->vars->mailer['port'] ?? '');
            $settings['username'] = strval($this->vars->mailer['username'] ?? '');
            $settings['password'] = strval($this->vars->mailer['password'] ?? '');
            $settings['tls'] = strval($this->vars->mailer['tls'] ?? '');
            $settings['dkim_key_path'] = strval($this->vars->mailer['dkim_key_path'] ?? '');
            $settings['dkim_domain'] = strval($this->vars->mailer['dkim_domain'] ?? '');
            $settings['dkim_selector'] = strval($this->vars->mailer['dkim_selector'] ?? '');
        }
        switch ($settings['type']) {
            case 'socket_SMTP':
            case 'symfony':
                $settings['type'] = 'symfony';
                break;
            case 'native':
            case 'sendmail':
                // These will be available as new options.
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
    private function altMail(Address $to, string $subject, string $message, Headers $additional_headers, Address $from, bool $messageIsHTML, string $charset, bool $debug): bool
    {
        $set = $this->getSettings();

        $message = str_replace(["\r\n", "\r", "\n"], ["\n", "\n", "\r\n"], $message);
        $subject = str_replace(["\r", "\n"], ['', ''], $subject);

        if ($set['type'] === 'default') {
            if ($messageIsHTML) {
                $content_type = 'text/html';
            } else {
                $content_type = 'text/plain';
            }
            $headers = [
                "Content-Type: $content_type; charset=$charset",
                $this->smtpHeaderFrom($from->getName(), $from->getAddress()),
            ];
            foreach ($additional_headers->all() as $header) {
                $headers[] = $header->toString();
            }
            $headers = implode("\r\n", $headers);
            $params = '-f ' . $from->getAddress();
            $return = mail($to->toString(), $subject, $message, $headers, $params);
            if (! $return) {
                trigger_error($this->vars->lang['emailErrorPhp'], E_USER_WARNING);
            }
            return $return;            
        } else {
            if (! isset($this->mailer)) {
                switch ($set['type']) {
                    case 'symfony':
                        if ($set['username'] !== '') {
                            $password = $set['password'];
                            if ($password !== '') {
                                $password = ':' . urlencode($password);
                            }
                            $login = urlencode($set['username']) . $password . '@';
                        } else {
                            $login = '';
                        }
                        $host = urlencode($set['host']) . ':' . (string) $set['port'];
                        switch ($set['tls']) {
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
                        break;

                    case 'native':
                        $transport = Transport::fromDsn("native://default");
                        break;

                    default:
                        $transport = Transport::fromDsn("sendmail://default");
                }
                $this->mailer = new Mailer($transport);
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
            $headers = $email->getHeaders();
            foreach ($additional_headers->all() as $header) {
                $headers->add($header);
            }

            if (! empty($set['dkim_key_path']) && ! empty($set['dkim_domain']) && ! empty($set['dkim_selector'])) {
                $key = 'file://' . $set['dkim_key_path'];
                $signer = new DkimSigner($key, $set['dkim_domain'], $set['dkim_selector']);
                $email = $signer->sign($email);
            }

            try {
                $this->mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                if ($debug) {
                    throw $e;
                } else {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                    return false;
                }
            }
        }
        return true;
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
    public function send(string $to, string $subject, string $message, string $charset, bool $html = false, bool $debug = false): bool
    {
        $rawbbname = rawHTML($this->vars->settings['bbname']);
        $rawusername = rawHTML($this->vars->self['username'] ?? '');
        $rawfrom = rawHTML($this->vars->settings['adminemail']);

        if (PHP_OS_FAMILY == 'Windows') {  // Official XMB hack for PHP bug #45305 a.k.a. #28038
            ini_set('sendmail_from', $rawfrom);
        }

        $from = new Address($rawfrom, $rawbbname);
        $toAddress = new Address($to);

        $headers = new Headers();
        $headers->addTextHeader('X-Mailer', 'PHP');
        $headers->addTextHeader('X-AntiAbuse', 'Board servername - ' . $this->vars->cookiedomain . ", Username - $rawusername");

        return $this->altMail($toAddress, $subject, $message, $headers, $from, $html, $charset, $debug);
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
