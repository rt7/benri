<?php
/**
 * douggr/benri
 *
 * @license http://opensource.org/license/MIT
 * @link    https://github.com/douggr/benri
 * @version 1.0.0
 */

/**
 * Representation of date and time.
 *
 * @link https://php.net/manual/en/class.datetime.php DateTime
 */
class Benri_Util_DateTime extends DateTime
{
    /**
     * The format of the output date string.
     *
     * Valid formats are explained in
     * [Date and Time Formats](https://php.net/manual/en/datetime.formats.php).
     *
     * @var string
     */
    private $_format = parent::ISO8601;

    /**
    * Returns date formatted according to given format.
    *
    * If `$format` is `null`, it'll use `Benri_Util_DateTime::$_format`.
    *
    * @param string $format Format accepted by
    *  [date()](https://php.net/manual/en/function.date.php#refsect1-function.date-parameters)
    * @return string
    * @see Benri_Util_DateTime::setFormat() Benri_Util_DateTime::setFormat()
    */
    public function format($format = null)
    {
        if (null === $format) {
            $format = $this->_format;
        }

        return parent::format($format);
    }

    /**
     * Getter for `Benri_Util_DateTime::$_format` property.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Convert number of seconds into a DateInterval object.
     *
     * @param int $seconds Number of seconds to parse
     * @return DateInterval
     * @see DateInterval https://php.net/DateInterval
     */
    public static function secondsToInterval($seconds)
    {
        $epoch = new static('@0');
        return $epoch->diff(new static("@$seconds"));
    }

    /**
    * Setter for `Benri_Util_DateTime::$_format`.
    *
    * @param string $format Format accepted by
    *  [date()](https://php.net/manual/en/function.date.php#refsect1-function.date-parameters)
    * @return Benri_Util_DateTime
    */
    public function setFormat($format)
    {
        $this->_format = $format;

        return $this;
    }

    /**
    * Returns new Benri_Util_DateTime object.
    *
    * @param string $time A date/time string
    *   Format accepted by
    *   [date()](https://php.net/manual/en/function.date.php#refsect1-function.date-parameters).
    * @param DateTimeZone $timezone A
    *   [DateTimeZone](https://php.net/manual/en/class.datetimezone.php)
    *   object representing the timezone
    * @return Benri_Util_DateTime A new Benri_Util_DateTime instance
    * @see Benri_Util_DateTime::format() Benri_Util_DateTime::format()
    */
    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        if (preg_match('/[\d+]{10}/', $time)) {
            return static::createFromFormat('U', $time, $timezone);
        } else {
            return parent::__construct($time, $timezone);
        }
    }

    /**
     * @return string
     * @see Benri_Util_DateTime::format() Benri_Util_DateTime::format()
     */
    public function __toString()
    {
        return $this->format();
    }
}
