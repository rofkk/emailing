<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * UpdateWorkerFor_1_8_0
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.8.0
 */

class UpdateWorkerFor_1_8_0 extends UpdateWorkerAbstract
{
    public function run()
    {
        // run the sql from file
        $this->runQueriesFromSqlFile('1.8.0');
    }
}