<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * UpdateWorkerAbstract
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.2
 */

abstract class UpdateWorkerAbstract extends CApplicationComponent
{
    /**
     * @return CDbConnection
     */
    final public function getDb(): CDbConnection
    {
        return db();
    }

    /**
     * @return string
     */
    final public function getTablePrefix(): string
    {
        return $this->getDb()->tablePrefix;
    }

    /**
     * @return string
     */
    final public function getSqlFilesPath(): string
    {
        return Yii::getPathOfAlias('common.data.update-sql');
    }

    /**
     * @param string $version
     *
     * @return bool
     * @throws CDbException
     */
    public function runQueriesFromSqlFile(string $version): bool
    {
        if (!is_file($sqlFile = $this->getSqlFilesPath() . '/' . $version . '.sql')) {
            return false;
        }

        foreach (CommonHelper::getQueriesFromSqlFile($sqlFile, $this->getTablePrefix()) as $query) {
            $this->getDb()->createCommand($query)->execute();
        }

        return true;
    }

    /**
     * @return mixed
     */
    abstract public function run();
}
