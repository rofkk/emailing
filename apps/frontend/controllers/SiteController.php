<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * SiteController
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class SiteController extends Controller
{
    /**
     * The landing page
     *
     * @return void
     */
    public function actionIndex()
    {
        if (!container()->get(OptionCommon::class)->getFrontendHomepage()) {
            $this->redirect(apps()->getAppUrl('customer'));
        }

        $this->setData([
            'pageMetaTitle' => $this->getData('pageMetaTitle') . ' | ' . t('app', 'Welcome'),
        ]);

        $view = 'index';
        if ($this->getViewFile($view . '-custom') !== false) {
            $view .= '-custom';
        }

        $this->render($view, [
            'siteName' => container()->get(OptionCommon::class)->getSiteName(),
        ]);
    }

    /**
     * @return void
     * @throws CHttpException
     */
    public function actionOffline()
    {
        $common = container()->get(OptionCommon::class);
        if ($common->getIsSiteOnline()) {
            $this->redirect(['site/index']);
        }

        throw new CHttpException(503, $common->getSiteOfflineMessage());
    }

    /**
     * Error handler
     *
     * @return void
     */
    public function actionError()
    {
        if ($error = app()->getErrorHandler()->error) {
            if (request()->getIsAjaxRequest()) {
                echo html_encode((string)$error['message']);
            } else {
                $this->setData([
                    'pageMetaTitle'         => t('app', 'Error {code}!', ['{code}' => (int)$error['code']]),
                    'pageMetaDescription'   => html_encode((string)$error['message']),
                ]);
                $this->render('error', $error);
            }
        }
    }
}
