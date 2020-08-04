<?php
declare(strict_types=1);

namespace Khue\VietDateTime\Framework\Stdlib\DateTime;

use Magento\Backend\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Timezone
 * @package Khue\VietDateTime\Framework\Stdlib\DateTime
 */
class Timezone extends \Magento\Framework\Stdlib\DateTime\Timezone
{
    /**
     * @var Session
     */
    protected $_session;

    public function __construct(
        ScopeResolverInterface $scopeResolver,
        ResolverInterface $localeResolver,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        $scopeType,
        $defaultTimezonePath,
        Session $session
    ) {
        $this->_session = $session;
        parent::__construct($scopeResolver, $localeResolver, $dateTime, $scopeConfig, $scopeType, $defaultTimezonePath);
    }

    /**
     * @param null $date
     * @param null $locale
     * @param bool $useTimezone
     * @param bool $includeTime
     * @return \DateTime
     * @throws \Exception
     */
    public function date($date = null, $locale = null, $useTimezone = true, $includeTime = true)
    {
        $locale = $locale ?: $this->_localeResolver->getLocale();
        $timezone = $useTimezone
            ? $this->getConfigTimezone()
            : date_default_timezone_get();

        switch (true) {
            case (empty($date)):
                return new \DateTime('now', new \DateTimeZone($timezone));
            case ($date instanceof \DateTime):
                return $date->setTimezone(new \DateTimeZone($timezone));
            case ($date instanceof \DateTimeImmutable):
                return new \DateTime($date->format('Y-m-d H:i:s'), $date->getTimezone());
            case (!is_numeric($date)):

                if (!$this->_session->getDateReformatted() && $locale == 'vi_VN') {
                    $tmpDate = $date;
                    $date = \DateTime::createFromFormat('d/m/Y', $date);

                    if ($date) {
                        $date = $date->format('m/d/Y 12:00:00');
                    } else {
                        $date = $tmpDate;
                    }
                }

                if ($locale == 'vi_VN') {
                    $locale = 'en_US';
                }

                $timeType = $includeTime ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE;
                $formatter = new \IntlDateFormatter(
                    $locale,
                    \IntlDateFormatter::SHORT,
                    $timeType,
                    new \DateTimeZone($timezone)
                );

                $date = $this->appendTimeIfNeeded($date, $includeTime);
                $date = $formatter->parse($date) ?: (new \DateTime($date))->getTimestamp();
                break;
        }

        return (new \DateTime('now', new \DateTimeZone($timezone)))->setTimestamp($date);
    }

    /**
     * Retrieve date with time
     *
     * @param string $date
     * @param bool $includeTime
     * @return string
     */
    private function appendTimeIfNeeded($date, $includeTime)
    {
        if ($includeTime && !preg_match('/\d{1}:\d{2}/', $date)) {
            $date .= " 0:00am";
        }
        return $date;
    }

    /**
     * @param $date
     * @param string $format
     * @return bool
     */
    private function validateDatetime($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}