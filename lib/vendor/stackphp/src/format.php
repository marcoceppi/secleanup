<?php

/// Options and tools for formatting table values.
class Format
{
    //====================
    // Formatting options
    //====================
    
    /// No formatting will be applied.
    const None = 0;
    /// Item will be shown as a hyperlink.
    const Hyperlink = 1;
    /// Item will be shown as an image.
    const Image = 2;
    /// Item will be shown as an icon (32x32).
    const Icon = 3;
    /// Date will be shown in RFC 822 format.
    const FullDate = 4;
    /// Date will use relative times.
    const RelativeDate = 5;
    
    //======================
    // Date / time constants
    //======================
    
    /// One hour
    const Hour  = 3600;
    /// One day
    const Day   = 86400;
    /// One month
    const Month = 2592000;
    /// One year
    const Year  = 31536000;
    
    // Ensures that units are correctly pluralized
    private static function Pluralize($amount, $unit)
    {
        if($amount == 1)
            return "$amount $unit ago";
        else
            return "$amount {$unit}s ago";
    }
    
    // Returns the relative time for a supplied timestamp
    private static function RelativeTime($timestamp)
    {
        // Calculate the difference
        $current_time = time();
        $difference = $current_time - $timestamp;
        
        if($difference == 0)
            return 'just now';
        elseif($difference < 60)
            return self::Pluralize($difference, 'second');
        elseif($difference < self::Hour)
            return self::Pluralize(floor($difference / 60), 'minute');
        elseif($difference < self::Day)
            return self::Pluralize(floor($difference / self::Hour), 'hour');
        elseif($difference < (self::Day * 2))
            return 'yesterday';
        elseif($difference < self::Month)
            return self::Pluralize(floor($difference / self::Day), 'day');
        elseif($difference < self::Year)
            return self::Pluralize(floor($difference / self::Month), 'month');
        else
            return self::Pluralize(floor($difference / self::Year), 'year');
    }
    
    /// Applies the given formatting to the specified value.
    /**
      * \param $value the value being formatted
      * \param $format the format to apply to the value
      * \return the formatted value
      */
    public static function Apply($value, $format)
    {
        switch($format)
        {
            case self::Hyperlink:
                return "<a href='$value'>$value</a>";
            case self::Image:
                return "<img src='$value' />";
            case self::Icon:
                return "<img src='$value' style='width: 32px; height: 32px;' />";
            case self::FullDate:
                return date(DATE_RFC822, $value);
            case self::RelativeDate:
                return self::RelativeTime($value);
            case self::None:  // fall-thru
            default:
                return $value;
        }
    }
}

?>