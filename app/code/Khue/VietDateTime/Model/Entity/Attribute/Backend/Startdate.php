<?php
declare(strict_types=1);

namespace Khue\VietDateTime\Model\Entity\Attribute\Backend;

use Magento\Backend\Model\Session;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Framework\Stdlib\DateTime\DateTime as LibDateTime;

/**
 * Class Startdate
 * @package Khue\VietDateTime\Model\Entity\Attribute\Backend
 */
class Startdate extends Datetime
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * Constructor
     *
     * @param TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ResolverInterface $localeResolver
     * @param Session $session
     */
    public function __construct(
        TimezoneInterface $localeDate,
        LibDateTime $date,
        ResolverInterface $localeResolver,
        Session $session
    ) {
        $this->_date = $date;
        $this->_localeResolver = $localeResolver;
        $this->_session = $session;

        parent::__construct($localeDate, $localeResolver, $session);
    }

    /**
     * Get attribute value for save.
     *
     * @param \Magento\Framework\DataObject $object
     * @return string|bool
     */
    protected function _getValueForSave($object)
    {
        $attributeName = $this->getAttribute()->getName();

        return $object->getData($attributeName);
    }

    /**
     * Before save hook.
     * Prepare attribute value for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $startDate = $this->_getValueForSave($object);
        if ($startDate === false) {
            return $this;
        }

        $object->setData($this->getAttribute()->getName(), $startDate);
        parent::beforeSave($object);
        return $this;
    }

    /**
     * Product from date attribute validate function.
     * In case invalid data throws exception.
     *
     * @param \Magento\Framework\DataObject $object
     * @throws \Magento\Eav\Model\Entity\Attribute\Exception
     * @return bool
     */
    public function validate($object)
    {
        $attr = $this->getAttribute();
        $maxDate = $attr->getMaxValue();
        $startDate = $this->_getValueForSave($object);

        if ($startDate === false) {
            return true;
        }

        if ($maxDate) {
            $date = $this->_date;
            $value = $date->timestamp($startDate);
            $maxValue = $date->timestamp($maxDate);

            if ($value > $maxValue) {
                $message = __('Make sure the To Date is later than or the same as the From Date.');
                $eavExc = new \Magento\Eav\Model\Entity\Attribute\Exception($message);
                $eavExc->setAttributeCode($attr->getName());

                throw $eavExc;
            }
        }

        return true;
    }
}