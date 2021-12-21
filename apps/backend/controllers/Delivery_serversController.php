<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * Delivery_serversController
 *
 * Handles the actions for delivery servers related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class Delivery_serversController extends Controller
{
    /**
     * @return void
     */
    public function init()
    {
        $this->addPageScript(['src' => AssetsUrl::js('delivery-servers.js')]);
        $this->onBeforeAction = [$this, '_registerJuiBs'];
        parent::init();
    }

    /**
     * @return array
     */
    public function filters()
    {
        $filters = [
            'postOnly + delete, validate, copy, enable, disable',
        ];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available delivery servers
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $server = new DeliveryServer('search');
        $server->unsetAttributes();
        $server->attributes = (array)request()->getQuery($server->getModelName(), []);

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('servers', 'View delivery servers'),
            'pageHeading'     => t('servers', 'View delivery servers'),
            'pageBreadcrumbs' => [
                t('servers', 'Delivery servers') => createUrl('delivery_servers/index'),
                t('app', 'View all'),
            ],
        ]);

        $types     = DeliveryServer::getTypesMapping();
        $csvImport = new DeliveryServerCsvImport();

        $this->render('list', compact('server', 'types', 'csvImport'));
    }

    /**
     * Create a new delivery server
     *
     * @param string $type
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionCreate($type)
    {
        $types = DeliveryServer::getTypesMapping();

        if (!isset($types[$type])) {
            throw new CHttpException(500, t('servers', 'Server type not allowed.'));
        }

        $modelClass = $types[$type];

        /** @var DeliveryServer $server */
        $server = new $modelClass();
        $server->type = $type;

        if (($failureMessage = $server->requirementsFailedMessage())) {
            notify()->addWarning($failureMessage);
            $this->redirect(['delivery_servers/index']);
        }

        $policy   = new DeliveryServerDomainPolicy();
        $policies = [];

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($server->getModelName(), []))) {
            if (!$server->isNewRecord && empty($attributes['password']) && isset($attributes['password'])) {
                unset($attributes['password']);
            }
            $server->attributes = $attributes;

            if ($policiesAttributes = (array)request()->getPost($policy->getModelName(), [])) {
                foreach ($policiesAttributes as $attributes) {
                    $policyModel = new DeliveryServerDomainPolicy();
                    $policyModel->attributes = $attributes;
                    $policies[] = $policyModel;
                }
            }

            if (!$server->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                if (!empty($policies)) {
                    foreach ($policies as $policyModel) {
                        $policyModel->server_id = (int)$server->server_id;
                        $policyModel->save();
                    }
                }
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller'=> $this,
                'success'   => notify()->getHasSuccess(),
                'server'    => $server,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['delivery_servers/update', 'type' => $type, 'id' => $server->server_id]);
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('servers', 'Create new server'),
            'pageHeading'     => t('servers', 'Create new delivery server'),
            'pageBreadcrumbs' => [
                t('servers', 'Delivery servers') => createUrl('delivery_servers/index'),
                t('app', 'Create new'),
            ],
        ]);

        // 1.3.9.5
        $view = hooks()->applyFilters('delivery_servers_form_view_file', 'form-' . $type, $server, $this);

        $this->render($view, compact('server', 'policy', 'policies'));
    }

    /**
     * Update existing delivery server
     *
     * @param string $type
     * @param int $id
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpdate($type, $id)
    {
        $types = DeliveryServer::getTypesMapping();

        if (!isset($types[$type])) {
            throw new CHttpException(500, t('servers', 'Server type not allowed.'));
        }

        $server = DeliveryServer::model($types[$type])->findByAttributes([
            'server_id' => (int)$id,
            'type'      => $type,
        ]);

        if (empty($server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if (!$server->getCanBeUpdated()) {
            $this->redirect(['delivery_servers/index']);
        }

        if (($failureMessage = $server->requirementsFailedMessage())) {
            notify()->addWarning($failureMessage);
            $this->redirect(['delivery_servers/index']);
        }

        $policy   = new DeliveryServerDomainPolicy();
        $policies = DeliveryServerDomainPolicy::model()->findAllByAttributes(['server_id' => $server->server_id]);

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($server->getModelName(), []))) {
            if (!$server->isNewRecord && empty($attributes['password']) && isset($attributes['password'])) {
                unset($attributes['password']);
            }
            $server->additional_headers = [];
            $server->attributes         = $attributes;

            $policies = [];
            if ($policiesAttributes = (array)request()->getPost($policy->getModelName(), [])) {
                foreach ($policiesAttributes as $attributes) {
                    $policyModel = new DeliveryServerDomainPolicy();
                    $policyModel->attributes = $attributes;
                    $policies[] = $policyModel;
                }
            }

            if (!$server->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                DeliveryServerDomainPolicy::model()->deleteAllByAttributes(['server_id' => $server->server_id]);
                if (!empty($policies)) {
                    foreach ($policies as $policyModel) {
                        $policyModel->server_id = (int)$server->server_id;
                        $policyModel->save();
                    }
                }
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller'=> $this,
                'success'   => notify()->getHasSuccess(),
                'server'    => $server,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['delivery_servers/update', 'type' => $type, 'id' => $server->server_id]);
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('servers', 'Update server'),
            'pageHeading'     => t('servers', 'Update delivery server'),
            'pageBreadcrumbs' => [
                t('servers', 'Delivery servers') => createUrl('delivery_servers/index'),
                t('app', 'Update'),
            ],
        ]);

        // 1.3.9.5
        $view = hooks()->applyFilters('delivery_servers_form_view_file', 'form-' . $type, $server, $this);

        $this->render($view, compact('server', 'policy', 'policies'));
    }

    /**
     * Delete existing delivery server
     *
     * @param int $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionDelete($id)
    {
        $_server = DeliveryServer::model()->findByPk((int)$id);
        if (empty($_server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (isset($mapping[$_server->type])) {
            $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
        } else {
            $server = DeliveryServer::model()->findByPk((int)$_server->server_id);
        }

        if (empty($server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if ($server->getCanBeDeleted()) {
            $server->delete();
        }

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('app', 'The item has been successfully deleted!'));
            $redirect = request()->getPost('returnUrl', ['delivery_servers/index']);
        }

        // since 1.3.5.9
        hooks()->doAction('controller_action_delete_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'model'      => $server,
            'redirect'   => $redirect,
        ]));

        if ($collection->itemAt('redirect')) {
            $this->redirect($collection->itemAt('redirect'));
        }
    }

    /**
     * Run a bulk action against the delivery servers
     *
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function actionBulk_action()
    {
        $mapping = DeliveryServer::getTypesMapping();
        $action  = request()->getPost('bulk_action');
        $items   = array_unique(array_map('intval', (array)request()->getPost('bulk_item', [])));

        if ($action == DeliveryServer::BULK_ACTION_DELETE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                $_server = DeliveryServer::model()->findByPk((int)$item);
                if (!isset($mapping[$_server->type])) {
                    continue;
                }

                $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
                if (empty($server)) {
                    continue;
                }

                if (!$server->getCanBeDeleted()) {
                    continue;
                }

                $server->delete();
                $affected++;
            }
            if ($affected) {
                notify()->addSuccess(t('app', 'The action has been successfully completed!'));
            }
        }

        $defaultReturn = request()->getServer('HTTP_REFERER', ['delivery_servers/index']);
        $this->redirect(request()->getPost('returnUrl', $defaultReturn));
    }

    /**
     * Validate a delivery server.
     * The delivery server will stay inactive until validation by email.
     * While delivery server is inactive it cannot be used to send emails
     *
     * @param int $id
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionValidate($id)
    {
        if (!($email = request()->getPost('email'))) {
            throw new CHttpException(500, t('servers', 'The email address is missing.'));
        }

        $_server = DeliveryServer::model()->findByPk((int)$id);

        if (empty($_server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if (!FilterVarHelper::email($email)) {
            throw new CHttpException(500, t('app', 'The email address you provided does not seem to be valid.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
        $server->confirmation_key = StringHelper::randomSha1();
        $server->save(false);

        $params = CommonEmailTemplate::getAsParamsArrayBySlug(
            'delivery-server-validation',
            [
                'to'      => $email,
                'subject' => t('servers', 'Please validate this server.'),
            ],
            [
                '[HOSTNAME]'         => $server->hostname,
                '[CONFIRMATION_URL]' => createAbsoluteUrl('delivery_servers/confirm', ['key' => $server->confirmation_key]),
                '[CONFIRMATION_KEY]' => $server->confirmation_key,
            ]
        );

        $params = $server->getParamsArray($params);

        if ($server->sendEmail($params)) {
            notify()->addSuccess(t('servers', 'Please check your mailbox to confirm the server.'));
            $redirect = ['delivery_servers/index'];
        } else {
            $dump = t('servers', 'Internal failure, maybe due to missing functions like {functions}!', ['{functions}' => 'proc_open']);
            if ($log = $server->getMailer()->getLog()) {
                $dump = $log;
            }
            if (preg_match('/\+\+\sSwift_SmtpTransport\sstarted.*/s', $dump, $matches)) {
                $dump = $matches[0];
            }
            $dump = html_encode((string)str_replace("\n\n", "\n", $dump));
            $dump = nl2br($dump);
            notify()->addError(t('servers', 'Cannot send the confirmation email using the data you provided.'));
            notify()->addWarning(t('servers', 'Here is a transcript of the error message:') . '<hr />');
            notify()->addWarning($dump);

            $redirect = ['delivery_servers/update', 'type' => $server->type, 'id' => $server->server_id];
        }

        $this->redirect($redirect);
    }

    /**
     * Confirm the validation of a delivery server.
     * This is accessed from the validation email and changes
     * the status of a delivery server from inactive in active thus allowing the application to send
     * emails using this server
     *
     * @param string $key
     *
     * @return void
     * @throws CHttpException
     */
    public function actionConfirm($key)
    {
        $_server = DeliveryServer::model()->findByAttributes([
            'confirmation_key' => $key,
        ]);

        if (empty($_server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);

        if (empty($server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $server->status = DeliveryServer::STATUS_ACTIVE;
        $server->confirmation_key = null;
        $server->save(false);

        if (!empty($server->hostname)) {
            notify()->addSuccess(t('servers', 'You have successfully confirmed the server {serverName}.', [
                '{serverName}' => $server->hostname,
            ]));
        } else {
            notify()->addSuccess(t('servers', 'The server has been successfully confirmed!'));
        }

        $this->redirect(['delivery_servers/index']);
    }

    /**
     * Create a copy of an existing delivery server
     *
     * @param int $id
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionCopy($id)
    {
        $_server = DeliveryServer::model()->findByPk((int)$id);
        if (empty($_server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
        if (empty($server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if ($server->copy()) {
            notify()->addSuccess(t('servers', 'Your server has been successfully copied!'));
        } else {
            notify()->addError(t('servers', 'Unable to copy the server!'));
        }

        if (!request()->getIsAjaxRequest()) {
            $this->redirect(request()->getPost('returnUrl', ['delivery_servers/index']));
        }
    }

    /**
     * Enable a server that has been previously disabled
     *
     * @param int $id
     *
     * @return void
     * @throws CHttpException
     */
    public function actionEnable($id)
    {
        $_server = DeliveryServer::model()->findByPk((int)$id);
        if (empty($_server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
        if (empty($server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if ($server->getIsDisabled()) {
            $server->enable();
            notify()->addSuccess(t('servers', 'Your server has been successfully enabled!'));
        } else {
            notify()->addError(t('servers', 'The server must be disabled in order to enable it!'));
        }

        if (!request()->getIsAjaxRequest()) {
            $this->redirect(request()->getPost('returnUrl', ['delivery_servers/index']));
        }
    }

    /**
     * Disable a server that has been previously verified
     *
     * @param int $id
     *
     * @return void
     * @throws CHttpException
     */
    public function actionDisable($id)
    {
        $_server = DeliveryServer::model()->findByPk((int)$id);
        if (empty($_server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
        if (empty($server)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if ($server->getIsActive()) {
            $server->disable();
            notify()->addSuccess(t('servers', 'Your server has been successfully disabled!'));
        } else {
            notify()->addError(t('servers', 'The server must be active in order to disable it!'));
        }

        if (!request()->getIsAjaxRequest()) {
            $this->redirect(request()->getPost('returnUrl', ['delivery_servers/index']));
        }
    }

    /**
     * Export existing delivery servers
     *
     * @return void
     */
    public function actionExport()
    {
        $models = DeliveryServer::model()->findAll();

        if (empty($models)) {
            notify()->addError(t('app', 'There is no item available for export!'));
            $this->redirect(['index']);
        }

        // Set the download headers
        HeaderHelper::setDownloadHeaders('delivery-servers.csv');

        try {
            $csvWriter = League\Csv\Writer::createFromPath('php://output', 'w');

            $attributes = AttributeHelper::removeSpecialAttributes($models[0]->attributes, ['password']);

            /** @var callable $callback */
            $callback   = [$models[0], 'getAttributeLabel'];
            $attributes = array_map($callback, array_keys($attributes));

            $csvWriter->insertOne($attributes);

            foreach ($models as $model) {
                $attributes = AttributeHelper::removeSpecialAttributes($model->attributes, ['password']);
                $csvWriter->insertOne(array_values($attributes));
            }
        } catch (Exception $e) {
        }

        app()->end();
    }

    /**
     * Import new delivery servers
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function actionImport()
    {
        $redirect = ['delivery_servers/index'];

        if (!request()->getIsPostRequest()) {
            $this->redirect($redirect);
        }

        // helps for when the document has been created on a Macintosh computer
        if (!ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', '1');
        }

        $import = new DeliveryServerCsvImport('import');
        $import->file = CUploadedFile::getInstance($import, 'file');

        if (!$import->validate() || empty($import->file)) {
            notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            notify()->addError($import->shortErrors->getAllAsString());
            $this->redirect($redirect);
        }

        $csvReader = League\Csv\Reader::createFromPath($import->file->tempName, 'r');
        $csvReader->setHeaderOffset(0);
        $csvHeader = array_map('strtolower', array_map('trim', $csvReader->getHeader()));

        $totalRecords = 0;
        $totalImport  = 0;

        $mapping = DeliveryServer::getTypesMapping();

        foreach ($csvReader->getRecords($csvHeader) as $row) {
            $row = ioFilter()->stripPurify($row);

            ++$totalRecords;

            if (!isset($row['type'], $mapping[$row['type']])) {
                continue;
            }

            $className = $mapping[$row['type']];
            $model     = new $className();
            $model->attributes = $row;

            if ($model->save()) {
                $totalImport++;
            }
        }

        notify()->addSuccess(t('servers', 'Your file has been successfuly imported, from {count} records, {total} were imported!', [
            '{count}'   => $totalRecords,
            '{total}'   => $totalImport,
        ]));

        $this->redirect($redirect);
    }

    /**
     * @param CEvent $event
     *
     * @return void
     */
    public function _registerJuiBs(CEvent $event)
    {
        if (in_array($event->params['action']->id, ['create', 'update'])) {
            $this->addPageStyles([
                ['src' => apps()->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001],
            ]);
        }
    }
}
