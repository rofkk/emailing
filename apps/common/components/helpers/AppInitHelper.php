<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * AppInitHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class AppInitHelper
{
    /**
     * @var string
     */
    private static $_entryScriptUrl;

    /**
     * @var string
     */
    private static $_baseUrl;

    /**
     * @return string
     * @throws Exception
     */
    public static function getEntryScriptUrl(): string
    {
        if (self::$_entryScriptUrl === null) {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);

            if (basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
                self::$_entryScriptUrl = $_SERVER['SCRIPT_NAME'];
            } elseif (basename($_SERVER['PHP_SELF']) === $scriptName) {
                self::$_entryScriptUrl = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
                self::$_entryScriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            } elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
                self::$_entryScriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            } elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                self::$_entryScriptUrl = (string)str_replace('\\', '/', (string)str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            } else {
                throw new Exception('Unable to determine the entry script URL.');
            }
        }
        return self::$_entryScriptUrl;
    }

    /**
     * @param string $appendThis
     *
     * @return string
     * @throws Exception
     */
    public static function getBaseUrl(string $appendThis = ''): string
    {
        if (self::$_baseUrl === null) {
            self::$_baseUrl = rtrim(dirname(self::getEntryScriptUrl()), '\\/');
        }
        return self::$_baseUrl . (!empty($appendThis) ? '/' . trim((string)$appendThis, '/') : '');
    }

    /**
     * @return void
     */
    public static function fixRemoteAddress(): void
    {
        static $hasRan = false;
        if ($hasRan) {
            return;
        }
        $hasRan = true;

        // keep a reference
        $_SERVER['ORIGINAL_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];

        $keys = [
            'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED',
        ];

        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) {
                continue;
            }
            $ips = explode(',', $_SERVER[$key]);
            $ips = array_map('trim', $ips);
            foreach ($ips as $ip) {
                if (FilterVarHelper::ip($ip)) {
                    $_SERVER['REMOTE_ADDR'] = $ip;
                    return;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public static function isModRewriteEnabled(): bool
    {
        return CommonHelper::functionExists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : true;
    }

    /**
     * @return bool
     */
    public static function isSecureConnection(): bool
    {
        return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
    }
}
