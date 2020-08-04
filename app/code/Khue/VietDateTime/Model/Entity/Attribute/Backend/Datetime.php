<?php
declare(strict_types=1);

namespace Khue\VietDateTime\Model\Entity\Attribute\Backend;

use Magento\Backend\Model\Session;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Datetime
 * @package Khue\VietDateTime\Model\Entity\Attribute\Backend
 */
class Datetime extends \Magento\Eav\Model\Entity\Attribute\Backend\Datetime
{
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    public function __construct(
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        Session $session
    ) {
        $this->_localeResolver = $localeResolver;
        $this->_session = $session;
        parent::__construct($localeDate);
    }

    /**
     * Prepare date for save in DB
     *
     * string format used from input fields (all date input fields need apply locale settings)
     * int value can be declared in code (this meen whot we use valid date)
     *
     * @param string|int|\DateTimeInterface $date
     * @return string
     * @throws \Exception
     */
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }
        // unix timestamp given - simply instantiate date object
        if (is_scalar($date) && preg_match('/^[0-9]+$/', $date)) {
            $date = (new \DateTime())->setTimestamp($date);
        } elseif (!($date instanceof \DateTimeInterface)) {
            // normalized format expecting Y-m-d[ H:i:s]  - time is optional
            $locale = $this->_localeResolver->getLocale();

            if ($locale == 'vi_VN' && !$this->_session->getDateReformatted()) {
                $tmpDate = $date;
                $date = \DateTime::createFromFormat('d/m/Y', $date);

                if ($date) {
                    $date = $date->format('m/d/Y 12:00:00');
                } else {
                    $date = $tmpDate;
                }
            }

            $date = new \DateTime($date);
        }
        return $date->format('Y-m-d H:i:s');
    }
}