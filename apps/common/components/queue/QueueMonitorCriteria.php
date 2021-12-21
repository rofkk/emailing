<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.0.0
 */

abstract class QueueMonitorCriteria implements QueueMonitorCriteriaInterface
{
    /**
     * @return mixed
     */
    abstract public function getCriteria();
}
