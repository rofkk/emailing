<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * TrackingDomain
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */

/**
 * This is the model class for table "{{tracking_domain}}".
 *
 * The followings are the available columns in table '{{tracking_domain}}':
 * @property integer $domain_id
 * @property integer|string $customer_id
 * @property string $name
 * @property string $scheme
 * @property string|CDbExpression $date_added
 * @property string|CDbExpression $last_updated
 *
 * The followings are the available model relations:
 * @property DeliveryServer[] $deliveryServers
 * @property Customer $customer
 */
class TrackingDomain extends ActiveRecord
{
    /**
     * Flag for http scheme
     */
    const SCHEME_HTTP = 'http';

    /**
     * Flag for https scheme
     */
    const SCHEME_HTTPS = 'https';

    /**
     * @var int - whether we should skip dns validation.
     */
    public $skipValidation = 0;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{tracking_domain}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['name, scheme', 'required'],
            ['name', 'length', 'max'=> 255],
            ['name', '_validateDomainCname'],
            ['scheme', 'in', 'range' => array_keys($this->getSchemesList())],
            ['customer_id', 'exist', 'className' => Customer::class],

            ['customer_id', 'unsafe', 'on' => 'customer-insert, customer-update'],

            // The following rule is used by search().
            ['customer_id, name', 'safe', 'on'=>'search'],

            ['scheme, skipValidation', 'safe'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        $relations = [
            'deliveryServers' => [self::HAS_MANY, DeliveryServer::class, 'tracking_domain_id'],
            'customer'        => [self::BELONGS_TO, Customer::class, 'customer_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'domain_id'      => t('tracking_domains', 'Domain'),
            'customer_id'    => t('tracking_domains', 'Customer'),
            'name'           => t('tracking_domains', 'Name'),
            'scheme'         => t('tracking_domains', 'Scheme'),
            'skipValidation' => t('tracking_domains', 'Skip validation'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'name' => t('tracking_domains', 'tracking.your-domain.com'),
        ];

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'skipValidation' => t('tracking_domains', 'Please DO NOT SKIP validation unless you are 100% sure you know what you are doing.'),
            'scheme'         => t('tracking_domains', 'Choose HTTPS only if your tracking domain can also provide a valid SSL certificate, otherwise stick to regular HTTP.'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     * @throws CException
     */
    public function search()
    {
        $criteria = new CDbCriteria();

        if (!empty($this->customer_id)) {
            $customerId = (string)$this->customer_id;
            if (is_numeric($customerId)) {
                $criteria->compare('t.customer_id', $customerId);
            } else {
                $criteria->with = [
                    'customer' => [
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'CONCAT(customer.first_name, " ", customer.last_name) LIKE :name',
                        'params'    => [
                            ':name'    => '%' . $customerId . '%',
                        ],
                    ],
                ];
            }
        }

        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.scheme', $this->scheme);

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort'=>[
                'defaultOrder' => [
                    't.domain_id'  => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return TrackingDomain the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var TrackingDomain $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return array
     */
    public function getSchemesList(): array
    {
        return [
            self::SCHEME_HTTP  => 'HTTP',
            self::SCHEME_HTTPS => 'HTTPS',
        ];
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function _validateDomainCname(string $attribute, array $params = []): void
    {
        if ($this->hasErrors() || $this->skipValidation) {
            return;
        }
        $currentDomainName = parse_url(container()->get(OptionUrl::class)->getFrontendUrl(), PHP_URL_HOST);
        if (empty($currentDomainName)) {
            $this->addError($attribute, t('tracking_domains', 'Unable to get the current domain name!'));
            return;
        }
        $domainName = strpos($this->$attribute, 'http') !== 0 ? 'http://' . $this->$attribute : $this->$attribute;
        $domainName = parse_url($domainName, PHP_URL_HOST);
        if (empty($domainName)) {
            $this->addError($attribute, t('tracking_domains', 'Your specified domain name does not seem to be valid!'));
            return;
        }
        if (!CommonHelper::functionExists('dns_get_record')) {
            $this->addError($attribute, t('tracking_domains', 'Your PHP install does not contain the {function} function needed to query the DNS records!', [
                '{function}' => 'dns_get_record',
            ]));
            return;
        }
        $dnsRecords = (array)dns_get_record($domainName, DNS_ALL);
        $found = false;

        // cname first.
        foreach ($dnsRecords as $record) {
            if (!isset($record['host'], $record['type'], $record['target'])) {
                continue;
            }
            if ($record['host'] == $domainName && $record['type'] == 'CNAME' && $record['target'] == $currentDomainName) {
                $found = true;
                break;
            }
        }

        // subdomain second
        if (!$found) {
            foreach ($dnsRecords as $record) {
                if (!isset($record['host'], $record['type'], $record['ip'])) {
                    continue;
                }
                if ($record['type'] != 'A') {
                    continue;
                }
                $ipDomain = gethostbyname($domainName);
                if ($record['host'] == $domainName && $record['ip'] == $ipDomain) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $this->addError($attribute, t('tracking_domains', 'Cannot find a valid CNAME record for {domainName}! Remember, the CNAME of {domainName} must point to {currentDomain}!', [
                '{domainName}'    => $domainName,
                '{currentDomain}' => $currentDomainName,
            ]));
            return;
        }
    }
}
