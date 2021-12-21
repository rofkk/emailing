<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * AccessHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */

class AccessHelper
{
    /**
     * @param string|array $route
     *
     * @return bool
     */
    public static function hasRouteAccess($route): bool
    {
        $app = Yii::app();
        if ($app->apps->isAppName('backend') && $app->hasComponent('user') && $app->user->getId() && $app->user->getModel()) {
            return (bool)$app->user->getModel()->hasRouteAccess($route);
        }
        return true;
    }
}
