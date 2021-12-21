<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * DeliveryServerCsvImport
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */

class DeliveryServerCsvImport extends FormModel
{
    /**
     * @var CUploadedFile
     */
    public $file;

    /**
     * @var string
     */
    public $file_name;

    /**
     * @var int
     */
    public $file_size_limit = 5242880; // 5 mb by default

    /**
     * @return array
     */
    public function rules()
    {
        $mimes = null;
        if (container()->get(OptionImporter::class)->getCanCheckMimeType()) {

            /** @var FileExtensionMimes $extensionMimes */
            $extensionMimes = app()->getComponent('extensionMimes');

            /** @var array $mimes */
            $mimes = $extensionMimes->get('csv')->toArray();
        }

        $rules = [
            // array('file', 'required', 'on' => 'upload'),
            ['file', 'unsafe'],
            ['file', 'file', 'types' => ['csv'], 'mimeTypes' => $mimes, 'maxSize' => $this->file_size_limit, 'allowEmpty' => true],
            ['file_name', 'length', 'is' => 44],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return bool
     */
    public function upload()
    {
        // no reason to go further if there are errors.
        if (!$this->validate()) {
            return false;
        }

        $filePath = Yii::getPathOfAlias('common.runtime.delivery-server-import') . '/';
        if (!file_exists($filePath) && !mkdir($filePath, 0777, true)) {
            $this->addError('file', t('servers', 'Unable to create target directory!'));
            return false;
        }

        $this->file_name = StringHelper::randomSha1() . '.csv';

        if (!$this->file->saveAs($filePath . $this->file_name)) {
            $this->file_name = '';
            $this->addError('file', t('servers', 'Unable to move the uploaded file!'));
            return false;
        }

        if (!StringHelper::fixFileEncoding($filePath . $this->file_name)) {
            unlink($filePath . $this->file_name);
            $this->addError('file', t('servers', 'Your uploaded file is not using the UTF-8 charset. Please save it in UTF-8 then upload it again.'));
            $this->file_name = '';
            return false;
        }

        return true;
    }
}
