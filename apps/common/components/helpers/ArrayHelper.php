<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * ArrayHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

class ArrayHelper
{
    /**
     * @param array $array
     * @param array $keys
     * @return bool
     */
    public static function hasKeys(array $array, array $keys = []): bool
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }

        $okay = true;
        foreach ($keys as $key) {
            if (isset($array[$key]) || array_key_exists($key, $array)) {
                continue;
            }
            $okay = false;
            break;
        }

        return $okay;
    }
}
