<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * Transactional_emailsController
 *
 * Handles the CRUD actions for transactional emails.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

class Transactional_emailsController extends Controller
{
    /**
     * @return array
     */
    public function accessRules()
    {
        return [
            // allow all authenticated users on all actions
            ['allow', 'users' => ['@']],
            // deny all rule.
            ['deny'],
        ];
    }

    /**
     * Handles the listing of the transactional emails.
     * The listing is based on page number and number of templates per page.
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $perPage    = (int)request()->getQuery('per_page', 10);
        $page       = (int)request()->getQuery('page', 1);
        $maxPerPage = 50;
        $minPerPage = 10;

        if ($perPage < $minPerPage) {
            $perPage = $minPerPage;
        }

        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        if ($page < 1) {
            $page = 1;
        }

        $data = [
            'count'         => null,
            'total_pages'   => null,
            'current_page'  => null,
            'next_page'     => null,
            'prev_page'     => null,
            'records'       => [],
        ];

        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', (int)user()->getId());

        $count = TransactionalEmail::model()->count($criteria);

        if ($count == 0) {
            $this->renderJson([
                'status'    => 'success',
                'data'      => $data,
            ]);
        }

        $totalPages = ceil($count / $perPage);

        $data['count']          = $count;
        $data['current_page']   = $page;
        $data['next_page']      = $page < $totalPages ? $page + 1 : null;
        $data['prev_page']      = $page > 1 ? $page - 1 : null;
        $data['total_pages']    = $totalPages;

        $criteria->order    = 't.email_id DESC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;

        $emails = TransactionalEmail::model()->findAll($criteria);

        foreach ($emails as $email) {
            $attributes = $email->getAttributes();
            unset($attributes['email_id']);
            $data['records'][] = $attributes;
        }

        $this->renderJson([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }

    /**
     * Handles the listing of a single email.
     *
     * @param string $email_uid
     *
     * @return void
     * @throws CException
     */
    public function actionView($email_uid)
    {
        $email = TransactionalEmail::model()->findByAttributes([
            'email_uid'   => $email_uid,
            'customer_id' => (int)user()->getId(),
        ]);

        if (empty($email)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The email does not exist.'),
            ], 404);
        }

        $attributes = $email->getAttributes();
        unset($attributes['email_id']);

        $data = [
            'record' => $attributes,
        ];

        $this->renderJson([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }

    /**
     * Handles the creation of a new transactional email.
     *
     * @return void
     * @throws CException
     */
    public function actionCreate()
    {
        if (!request()->getIsPostRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only POST requests allowed for this endpoint.'),
            ], 400);
        }

        $attributes = (array)request()->getPost('email', []);

        $email = new TransactionalEmail();
        $email->attributes  = $attributes;
        $email->body        = !empty($email->body) ? (string)base64_decode($email->body) : '';
        $email->plain_text  = !empty($email->plain_text) ? (string)base64_decode($email->plain_text) : '';
        $email->customer_id = (int)user()->getId();

        if (!$email->save()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => $email->shortErrors->getAll(),
            ], 422);
        }

        $this->renderJson([
            'status'     => 'success',
            'email_uid'  => $email->email_uid,
        ], 201);
    }

    /**
     * Handles deleting an existing transactional email.
     *
     * @param string $email_uid
     *
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function actionDelete($email_uid)
    {
        if (!request()->getIsDeleteRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only DELETE requests allowed for this endpoint.'),
            ], 400);
        }

        $email = TransactionalEmail::model()->findByAttributes([
            'email_uid'   => $email_uid,
            'customer_id' => (int)user()->getId(),
        ]);

        if (empty($email)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The email does not exist.'),
            ], 404);
        }

        $email->delete();

        // since 1.3.5.9
        hooks()->doAction('controller_action_delete_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'model'      => $email,
        ]));

        $this->renderJson([
            'status' => 'success',
        ]);
    }

    /**
     * It will generate the timestamp that will be used to generate the ETAG for GET requests.
     *
     * @return int
     * @throws CException
     */
    public function generateLastModified()
    {
        static $lastModified;

        if ($lastModified !== null) {
            return $lastModified;
        }

        $row = [];

        if ($this->getAction()->getId() == 'index') {
            $perPage    = (int)request()->getQuery('per_page', 10);
            $page       = (int)request()->getQuery('page', 1);
            $maxPerPage = 50;
            $minPerPage = 10;

            if ($perPage < $minPerPage) {
                $perPage = $minPerPage;
            }

            if ($perPage > $maxPerPage) {
                $perPage = $maxPerPage;
            }

            if ($page < 1) {
                $page = 1;
            }

            $limit  = $perPage;
            $offset = ($page - 1) * $perPage;

            $sql = '
                SELECT AVG(t.last_updated) as `timestamp`
                FROM (
                     SELECT `a`.`customer_id`, UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                     FROM `{{transactional_email}}` `a`
                     WHERE `a`.`customer_id` = :cid
                     ORDER BY a.`email_id` DESC
                     LIMIT :l OFFSET :o
                ) AS t
                WHERE `t`.`customer_id` = :cid
            ';

            $command = db()->createCommand($sql);
            $command->bindValue(':cid', (int)user()->getId(), PDO::PARAM_INT);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);

            $row = $command->queryRow();
        } elseif ($this->getAction()->getId() == 'view') {
            $sql = 'SELECT UNIX_TIMESTAMP(t.last_updated) as `timestamp` FROM `{{transactional_email}}` t WHERE `t`.`email_uid` = :uid AND `t`.`customer_id` = :cid LIMIT 1';
            $command = db()->createCommand($sql);
            $command->bindValue(':uid', request()->getQuery('email_uid'), PDO::PARAM_STR);
            $command->bindValue(':cid', (int)user()->getId(), PDO::PARAM_INT);

            $row = $command->queryRow();
        }

        if (isset($row['timestamp'])) {
            $timestamp = round((float)$row['timestamp']);
            if (preg_match('/\.(\d+)/', (string)$row['timestamp'], $matches)) {
                $timestamp += (int)$matches[1];
            }
            return $lastModified = (int)$timestamp;
        }

        return $lastModified = parent::generateLastModified();
    }
}
