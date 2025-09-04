<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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

namespace XMB\Session;

use RuntimeException;
use XMB\Core;
use XMB\Password;
use XMB\SQL;
use XMB\Token;
use XMB\Validation;

use function XMB\formYesNo;
use function XMB\getPhpInput;
use function XMB\getRawString;

/**
 * Cookies are the default client storage system for session tokens in all versions of XMB.
 *
 * @since 1.9.12
 */
class FormsAndCookies implements Mechanism
{
    // Mechanism configuration.
    const FORM_EXP = 3600;
    const REGEN_AFTER = 3600;
    const REGEN_ENABLED = true;
    const SESSION_LIFE_LONG = 86400 * 30;
    const SESSION_LIFE_SHORT = 3600 * 12;
    const TEST_DATA = 'xmb';
    const TOKEN_BYTES = 16;
    const USER_MIN_LEN = 3;

    // Cookie names.
    const FORM_COOKIE = 'login';
    const REGEN_COOKIE = 'id2';
    const SESSION_COOKIE = 'xmbpw';
    const TEST_COOKIE = 'test';
    const USER_COOKIE = 'xmbuser';

    public function __construct(private Core $core, private Password $password, private SQL $sql, private Token $token, private Validation $validate)
    {
        // Property promotion.
    }

    public function checkUsername(): Data
    {
        $data = new Data();
        $uinput = $this->validate->postedVar('username', dbescape: false);

        if (strlen($uinput) < self::USER_MIN_LEN) {
            return $data;
        }

        $member = $this->sql->getMemberByName($uinput, includePassword: true);

        if (empty($member)) {
            $data->status = 'bad';
            return $data;
        }

        $data->password = $member['password'] !== '' ? $member['password'] : $member['password2'];
        unset($member['password'], $member['password2']);
        $data->member = &$member;
        $data->status = 'good';
        $data->comment = $this->validate->postedVar('comment', dbescape: false);
        $data->permanent = formYesNo('trust') == 'yes';
        return $data;
    }

    public function checkPassword(Data $data): Data
    {
        $pinput = getRawString('password');

        if (empty($pinput)) {
            return new Data();
        }

        $allowChanges = $this->core->schemaHasPasswordV2() && ! defined('XMB\UPGRADE');
        $result = $this->password->checkLogin($pinput, $data->password, $data->member['username'], $allowChanges);
        switch ($result) {
            case 'bad':
                $this->core->auditBadLogin($data->member);
                $data = new Data();
                $data->status = 'bad';
                break;
            case 'must-change':
                $data->pwReset = true;
        }

        return $data;
    }

    public function checkClientEnabled(): bool
    {
        $uinput = $this->get_cookie(self::USER_COOKIE);
        $test   = $this->get_cookie(self::TEST_COOKIE);

        if (strlen($uinput) >= self::USER_MIN_LEN || self::TEST_DATA == $test) {
            return true;
        } else {
            $this->core->put_cookie('test', self::TEST_DATA, time() + (86400*365));
            return false;
        }
    }

    public function checkSavedSession(): Data
    {
        $data = new Data();

        $pinput = $this->get_cookie(self::SESSION_COOKIE);
        $uinput = $this->get_cookie(self::USER_COOKIE);

        if (strlen($uinput) < self::USER_MIN_LEN || strlen($pinput) != self::TOKEN_BYTES * 2) {
            $data->status = 'none';
            return $data;
        }
        
        $member = $this->sql->getMemberByName($uinput);
        
        if (empty($member)) {
            $data->status = 'none';
            return $data;
        }
        
        $details = $this->sql->getSession($pinput, $uinput);

        if (empty($details)) {
            $this->core->auditBadSession($member);
            $data->status = 'bad';
            return $data;
        }
        
        if (time() > (int) $details['expire']) {
            $this->core->auditBadSession($member);
            $data->status = 'bad';
            return $data;
        }
        
        // Token Regeneration
        if (self::REGEN_ENABLED) {
            // Figure out where we are in the regeneration cycle.
            $cookie2 = $this->get_cookie(self::REGEN_COOKIE);
            if ($cookie2 != '' && $cookie2 === $details['replaces']) {
                // Normal: Client responded with both the new token and the old token. Ready to delete old token.
                $this->sql->deleteParentSession($details['replaces'], $details['token']);
                $details['replaces'] = '';
                if (time() > (int) $details['regenerate']) {
                    // Much time has passed between client's requests. Start a new cycle.
                    $this->regenerate($details);
                } else {
                    // Regeneration complete.
                    $this->delete_cookie(self::REGEN_COOKIE);
                }
            } elseif ($details['replaces'] != '') {
                // Abnormal: Client responded with the new token but doesn't posess the current (old) token.
                // Regeneration is compromised.  Both tokens must be destroyed.
                $this->sql->deleteSession($details['replaces']); // Delete old token
                $this->sql->deleteSessionReplacements($details['replaces']); // Delete new token and any orphans
                $this->core->auditBadSession($member);
                $data->status = 'bad';
                return $data;
            } elseif (time() > (int) $details['regenerate']) {
                // Current session needs to be regenerated.
                $newdetails = $this->sql->getSessionReplacements($pinput, $uinput);
                if (empty($newdetails)) {
                    // Normal: This is the first stale hit. New token is needed.
                    $this->regenerate($details);
                } else {
                    // Abnormal: Client responded with old token after new token was created.
                    // Caused by interruption or race conditions.
                    $this->recover($newdetails);
                }
            } elseif ($cookie2 != '') {
                // Abnormal: Client responded with both tokens after the old token was deleted.
                // Caused by interruption or race conditions.
                $this->delete_cookie(self::REGEN_COOKIE);
            } else {
                // Current session is stable.
            }
        }

        $data->member = &$member;
        $data->status = 'good';
        return $data;
    }

    public function logout(): Data
    {
        $data = $this->checkSavedSession();

        if ('good' == $data->status) {
            $token = $this->get_cookie(self::SESSION_COOKIE);

            $this->sql->deleteSession($token);
            $this->sql->deleteSessionReplacements($token);
            $this->deleteClientData();
            $data->status = 'logged-out';
            // $data->member passes through so the manager knows who is logging out.
        } elseif ('bad' == $data->status) {
            $this->deleteClientData();
            $data = new Data();
        } else {
            // There was no session.
        }

        return $data;
    }

    public function logoutAll(string $username, bool $current_client)
    {
        $this->sql->deleteSessionsByName($username);
        if ($current_client) {
            $this->deleteClientData();
        }
    }

    public function deleteClientData()
    {
        $this->delete_cookie(self::REGEN_COOKIE);
        $this->delete_cookie(self::SESSION_COOKIE);
        $this->delete_cookie(self::USER_COOKIE);
        $this->delete_cookie('oldtopics');
        $this->delete_cookie('xmblva');
        $this->delete_cookie('xmblvb');

        foreach ($_COOKIE as $key=>$val) {
            if (preg_match('#^fidpw([0-9]+)$#', $key)) {
                $this->delete_cookie($key);
            }
        }

        // Remember to check that these cookies will not be reset after initializing the session.
        // Maybe poison the function put_cookie() itself.
    }

    /**
     * Creates a new session token and cookies for a client who authenticated during this request.
     *
     * @param Data
     */
    public function saveClientData(Data $data): bool
    {
        // Create a new session here.
        $token = bin2hex(random_bytes(self::TOKEN_BYTES));

        if ($data->permanent) {
            $expires = time() + self::SESSION_LIFE_LONG;
        } else {
            $expires = time() + self::SESSION_LIFE_SHORT;
        }

        $regenerate = time() + self::REGEN_AFTER;

        $replaces = '';

        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';

        $sname = $this->core->schemaHasSessionNames() ? $data->comment : null;

        $success = $this->sql->saveSession($token, $data->member['username'], time(), $expires, $regenerate, $replaces, $agent, $sname);

        if (! $success) {
            // Retry once.
            $token = bin2hex(random_bytes(self::TOKEN_BYTES));
            $success = $this->sql->saveSession($token, $data->member['username'], time(), $expires, $regenerate, $replaces, $agent, $sname);
        }

        if (! $success) {
            throw new RuntimeException('XMB was unable to save a new session token.');
        }

        if (! $data->permanent) {
            $expires = 0;
        }

        $this->core->put_cookie(self::USER_COOKIE, $data->member['username'], $expires);
        $this->core->put_cookie(self::SESSION_COOKIE, $token, $expires);
        
        return true;
    }

    /**
     * Creates a new session token and cookies for a client whose session has become stale.
     *
     * @param array $oldsession
     */
    private function regenerate(array $oldsession)
    {
        $regenerate = time() + self::REGEN_AFTER;

        $replaces = $oldsession['token'];

        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';

        $sname = $this->core->schemaHasSessionNames() ? $oldsession['name'] : null;

        $token = bin2hex(random_bytes(self::TOKEN_BYTES));
        $success = $this->sql->saveSession($token, $oldsession['username'], (int) $oldsession['login_date'], (int) $oldsession['expire'], $regenerate, $replaces, $agent, $sname);

        if (! $success) {
            // Retry once.
            $token = bin2hex(random_bytes(self::TOKEN_BYTES));
            $success = $this->sql->saveSession($token, $oldsession['username'], (int) $oldsession['login_date'], (int) $oldsession['expire'], $regenerate, $replaces, $agent, $sname);
        }

        if (! $success) {
            throw new RuntimeException('XMB was unable to save a new session token.');
        }

        if ((int) $oldsession['expire'] > time() + self::SESSION_LIFE_SHORT) {
            $expires = (int) $oldsession['expire'];
        } else {
            $expires = 0;
        }

        $this->core->put_cookie(self::USER_COOKIE, $oldsession['username'], $expires);
        $this->core->put_cookie(self::SESSION_COOKIE, $token, $expires);
        $this->core->put_cookie(self::REGEN_COOKIE, $replaces, $expires);
    }

    /**
     * Resets session cookies for a client who has authenticated but lost the regeneration data.
     *
     * @param array $newSessions
     */
    private function recover(array $newSessions)
    {
        $newsession = $newSessions[0];
        if (count($newSessions > 1)) {
            // Abnormal: Client responded with old token after multiple new tokens were created.
            // Caused by race conditions.
            $this->sql->deleteSessionReplacements($newsession['replaces'], except: $newsession['token']);
        }
        if ((int) $newsession['expire'] > time() + self::SESSION_LIFE_SHORT) {
            $expires = (int) $newsession['expire'];
        } else {
            $expires = 0;
        }

        $this->core->put_cookie(self::USER_COOKIE, $newsession['username'], $expires);
        $this->core->put_cookie(self::SESSION_COOKIE, $newsession['token'], $expires);
        $this->core->put_cookie(self::REGEN_COOKIE, $newsession['replaces'], $expires);
    }

    /**
     * Deletes all expired tokens in the sessions table.
     */
    public function collectGarbage()
    {
        $this->sql->deleteSessionsByDate(time());
    }

    /**
     * Retrieve list of all valid sessions for the current user.
     *
     * @param string $username
     * @return array
     */
    public function getSessionList(string $username): array
    {
        $sessions = [];
        $pinput = $this->get_cookie(self::SESSION_COOKIE);

        $rows = $this->sql->getSessionsByName($username);
        foreach ($rows as $session) {
            if ((int) $session['expire'] < time()) {
                continue;
            }
            $session['current'] = ($pinput == $session['token']);
            $session['token'] = substr($session['token'], 0, 4);
            $sessions[] = $session;
        }

        return $sessions;
    }

    public function logoutByList(string $username, array $selection)
    {
        $pinput = $this->get_cookie(self::SESSION_COOKIE);
        if (! empty($selection)) {
            $this->sql->deleteSessionsByList($username, $selection, $pinput);
        }
    }

    /**
     * This event occurs when the client visits the login page to get ready for a login.
     *
     * @since 1.10.00
     */
    public function preLogin(string $newToken)
    {
        $this->core->put_cookie(self::FORM_COOKIE, $newToken, expire: time() + self::FORM_EXP);
    }

    /**
     * Check the origin of the login request to verify it has not been injected by a different domain.
     *
     * @since 1.10.00
     * @return bool
     */
    public function checkOrigin(): bool
    {
        if (! $this->core->schemaHasTokens()) return true;

        // Due to the anonymous nature of a login request, we need to check both the form integrity and the cookie integrity.
        $cookieToken = $this->get_cookie(self::FORM_COOKIE);
        $postToken = getPhpInput('token');
        $this->delete_cookie(self::FORM_COOKIE);
        
        if ($cookieToken != $postToken) return false;
        
        return $this->token->consume($postToken, 'Login', '');
    }

    private function get_cookie(string $name): string
    {
        return getPhpInput($name, sourcearray: 'c');
    }

    private function delete_cookie(string $name)
    {
        if ($this->get_cookie($name) != '') {
            $this->core->put_cookie($name);
        }
    }
}
