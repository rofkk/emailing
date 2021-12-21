<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * ListsController
 *
 * Handles the actions for lists related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.7
 */

class ListsController extends Controller
{
    /**
     * @return void
     */
    public function init()
    {
        $this->addPageScript(['src' => AssetsUrl::js('lists.js')]);
        $this->onBeforeAction = [$this, '_registerJuiBs'];
        parent::init();
    }

    /**
     * Show available lists
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $list = new Lists('search');
        $list->unsetAttributes();
        $list->attributes = (array)request()->getQuery($list->getModelName(), []);

        // 1.8.8
        $refreshRoute = ['lists/index'];
        $gridAjaxUrl  = createUrl($this->getRoute());
        if ($list->getIsArchived()) {
            $refreshRoute = ['lists/index', 'Lists[status]' => Lists::STATUS_ARCHIVED];
            $gridAjaxUrl = createUrl($this->getRoute(), ['Lists[status]' => Lists::STATUS_ARCHIVED]);
        }

        $this->setData([
            'refreshRoute' => $refreshRoute,
            'gridAjaxUrl'  => $gridAjaxUrl,
        ]);

        $pageHeading = t('lists', 'Lists');
        $breadcrumbs = [t('lists', 'Lists') => createUrl('lists/index')];
        if ($list->getIsArchived()) {
            $pageHeading = t('lists', 'Archived lists');
            $breadcrumbs[t('lists', 'Archived lists')] = createUrl('lists/index', ['Lists[status]' => Lists::STATUS_ARCHIVED]);
        }
        $breadcrumbs[] = t('app', 'View all');
        //

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('lists', 'Email lists'),
            'pageHeading'     => $pageHeading,
            'pageBreadcrumbs' => $breadcrumbs,
        ]);

        $this->render('list', compact('list'));
    }

    /**
     * Display list overview
     * This is a page containing shortcuts to the most important list features.
     *
     * @param string $list_uid
     *
     * @return void
     * @throws CHttpException
     */
    public function actionOverview($list_uid)
    {
        $list = $this->loadModel($list_uid);

        if ($list->getIsPendingDelete()) {
            $this->redirect(['lists/index']);
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('lists', 'List overview'),
            'pageHeading'     => t('lists', 'List overview'),
            'pageBreadcrumbs' => [
                t('lists', 'Lists') => createUrl('lists/index'),
                $list->name . ' ' => createUrl('lists/overview', ['list_uid' => $list->list_uid]),
                t('lists', 'Overview'),
            ],
        ]);

        $confirmedSubscribersCount = $list->getConfirmedSubscribersCount(true);
        $subscribersCount          = $list->getSubscribersCount(true);
        $segmentsCount             = $list->activeSegmentsCount;
        $customFieldsCount         = $list->fieldsCount;
        $pagesCount                = ListPageType::model()->count();

        $this->render('overview', compact(
            'list',
            'confirmedSubscribersCount',
            'subscribersCount',
            'segmentsCount',
            'customFieldsCount',
            'pagesCount'
        ));
    }

    /**
     * Toggle list as an archive
     *
     * @param string $list_uid
     *
     * @return void
     * @throws CHttpException
     */
    public function actionToggle_archive($list_uid)
    {
        /** @var Lists $list */
        $list = $this->loadModel((string)$list_uid);

        /** @var array $returnRoute */
        $returnRoute = ['lists/index'];

        if ($list->getIsPendingDelete()) {
            $this->redirect($returnRoute);
        }

        if ($list->getIsArchived()) {
            $list->saveStatus(Lists::STATUS_ACTIVE);
            notify()->addSuccess(t('lists', 'Your list was successfully unarchived!'));
            $returnRoute = ['lists/index'];
        } elseif (!$list->getIsArchived()) {
            $list->saveStatus(Lists::STATUS_ARCHIVED);
            notify()->addSuccess(t('lists', 'Your list was successfully archived!'));
            $returnRoute = ['lists/index', 'Lists[status]' => Lists::STATUS_ARCHIVED];
        }

        if (!request()->getIsAjaxRequest()) {
            $this->redirect(request()->getPost('returnUrl', $returnRoute));
        }
    }

    /**
     * Delete existing list
     *
     * @param string $list_uid
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionDelete($list_uid)
    {
        $list = $this->loadModel($list_uid);

        if (!$list->getIsRemovable()) {
            $this->redirect(['lists/index']);
        }

        if (request()->getIsPostRequest()) {
            $list->delete();

            /** @var Customer $customer */
            $customer = $list->customer;

            /** @var CustomerActionLogBehavior $logAction */
            $logAction = $customer->getLogAction();
            $logAction->listDeleted($list);

            notify()->addSuccess(t('app', 'Your item has been successfully deleted!'));
            $redirect = request()->getPost('returnUrl', ['lists/index']);

            // since 1.3.5.9
            hooks()->doAction('controller_action_delete_data', $collection = new CAttributeCollection([
                'controller' => $this,
                'model'      => $list,
                'redirect'   => $redirect,
            ]));

            if ($collection->itemAt('redirect')) {
                $this->redirect($collection->itemAt('redirect'));
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('lists', 'Confirm list removal'),
            'pageHeading'     => t('lists', 'Confirm list removal'),
            'pageBreadcrumbs' => [
                t('lists', 'Lists') => createUrl('lists/index'),
                $list->name . ' ' => createUrl('lists/overview', ['list_uid' => $list->list_uid]),
                t('lists', 'Confirm list removal'),
            ],
        ]);

        $campaign = new Campaign();
        $campaign->unsetAttributes();
        $campaign->attributes  = (array)request()->getQuery($campaign->getModelName(), []);
        $campaign->list_id     = (int)$list->list_id;

        $this->render('delete', compact('list', 'campaign'));
    }

    /**
     * Display a searchable table of subscribers from all lists
     *
     * @return void
     * @throws CException
     */
    public function actionAll_subscribers()
    {
        // filter instance to create the form
        $filter = new AllCustomersListsSubscribersFilters();
        $filter->user_id = (int)user()->getId();

        if ($attributes = (array)request()->getQuery('', [])) {
            $filter->attributes = CMap::mergeArray($filter->attributes, $attributes);
            $filter->hasSetFilters = true;
        }
        if ($attributes = (array)request()->getPost('', [])) {
            $filter->attributes = CMap::mergeArray($filter->attributes, $attributes);
            $filter->hasSetFilters = true;
        }

        if ($filter->hasSetFilters && !$filter->validate()) {
            notify()->addError($filter->shortErrors->getAllAsString());
            $this->redirect([$this->getRoute()]);
        }

        // 1.6.8
        if (!$filter->getIsViewAction()) {
            if (request()->getPost('confirm', null) === null) {
                $this->render('confirm-filters-action');
                return;
            }
            if (request()->getPost('confirm', '') !== 'true') {
                $this->redirect([$this->getRoute()]);
            }
        }
        //

        // the export action
        if ($filter->getIsExportAction()) {
            queue_send('backend.lists.allsubscribers.filter.export', $filter->getAttributes(array_merge($filter->getSafeAttributeNames(), ['user_id'])));
            notify()->addSuccess(t('app', 'Your request has been successfully queued, you will be notified once it is completed!'));
            $this->redirect([$this->getRoute()]);
        }

        // the confirm action
        if ($filter->getIsConfirmAction()) {
            queue_send('backend.lists.allsubscribers.filter.confirm', $filter->getAttributes(array_merge($filter->getSafeAttributeNames(), ['user_id'])));
            notify()->addSuccess(t('app', 'Your request has been successfully queued, you will be notified once it is completed!'));
            $this->redirect([$this->getRoute()]);
        }

        // the unsubscribe action
        if ($filter->getIsUnsubscribeAction()) {
            queue_send('backend.lists.allsubscribers.filter.unsubscribe', $filter->getAttributes(array_merge($filter->getSafeAttributeNames(), ['user_id'])));
            notify()->addSuccess(t('app', 'Your request has been successfully queued, you will be notified once it is completed!'));
            $this->redirect([$this->getRoute()]);
        }

        // the disable action
        if ($filter->getIsDisableAction()) {
            queue_send('backend.lists.allsubscribers.filter.disable', $filter->getAttributes(array_merge($filter->getSafeAttributeNames(), ['user_id'])));
            notify()->addSuccess(t('app', 'Your request has been successfully queued, you will be notified once it is completed!'));
            $this->redirect([$this->getRoute()]);
        }

        // the blacklist action
        if ($filter->getIsBlacklistAction()) {
            queue_send('backend.lists.allsubscribers.filter.blacklist', $filter->getAttributes(array_merge($filter->getSafeAttributeNames(), ['user_id'])));
            notify()->addSuccess(t('app', 'Your request has been successfully queued, you will be notified once it is completed!'));
            $this->redirect([$this->getRoute()]);
        }

        // the delete action
        if ($filter->getIsDeleteAction()) {
            queue_send('backend.lists.allsubscribers.filter.delete', $filter->getAttributes(array_merge($filter->getSafeAttributeNames(), ['user_id'])));
            notify()->addSuccess(t('app', 'Your request has been successfully queued, you will be notified once it is completed!'));
            $this->redirect([$this->getRoute()]);
        }

        // the view action, default one.
        $this->addPageScript(['src' => AssetsUrl::js('lists-all-subscribers.js')]);
        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('lists', 'Subscribers'),
            'pageHeading'     => t('lists', 'Subscribers from all your lists'),
            'pageBreadcrumbs' => [
                t('lists', 'Lists') => createUrl('lists/index'),
                t('lists', 'Subscribers'),
            ],
        ]);

        $this->render('all-subscribers', compact('filter'));
    }

    /**
     * @param string $list_uid
     *
     * @return Lists
     * @throws CHttpException
     */
    public function loadModel(string $list_uid): Lists
    {
        $criteria = new CDbCriteria();
        $criteria->compare('list_uid', $list_uid);
        $criteria->addNotInCondition('status', [Lists::STATUS_PENDING_DELETE]);

        $model = Lists::model()->find($criteria);

        if ($model === null) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if ($model->getIsPendingDelete()) {
            $this->redirect(['lists/index']);
        }

        return $model;
    }

    /**
     * @param CEvent $event
     *
     * @return void
     */
    public function _registerJuiBs(CEvent $event)
    {
        if (in_array($event->params['action']->id, ['all_subscribers'])) {
            $this->addPageStyles([
                ['src' => apps()->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001],
            ]);
        }
    }
}
