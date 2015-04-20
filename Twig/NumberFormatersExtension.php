<?php

namespace EPWT\XhprofBundle\Twig;

/**
 * Class NumberFormatersExtension
 * @package EPWT\XhprofBundle\Twig
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class NumberFormatersExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'epwt_xhprof_number_formaters';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('epwtXhprofMicroSeconds', [$this, 'microSecondsFilter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('epwtXhprofBytes', [$this, 'bytesFilter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('epwtXhprofFilter', [$this, 'baseFilter'], ['is_safe' => ['html']]),
        ];
    }

    public function microSecondsFilter($time, $format = true)
    {
        $pad    = false;
        $suffix = '&micro;s';
        if (abs($time) >= 1000) {
            $time   = $time / 1000;
            $suffix = 'ms';
            if (abs($time) >= 1000) {
                $pad    = true;
                $time   = $time / 1000;
                $suffix = 's';
                if (abs($time) >= 60) {
                    $time   = $time / 60;
                    $suffix = 'm';
                }
            }
        }
        if ($pad) {
            $time = sprintf('%.4f', $time);
        }

        if ($format) {
            return '<span class="value">' . $time . '</span> <span class="measure">' . $suffix . '</span>';
        } else {
            return $time . ' ' . $suffix;
        }
    }

    /**
     * @param $size
     * @param int $precision
     * @param bool $format
     *
     * @return int|string
     */
    public function bytesFilter($size, $precision = 2, $format = true)
    {
        $size = (int) $size;
        if ($size == 0) {
            return 0;
        }

        $base      = log(abs($size)) / log(1024);
        $suffixes  = ['b', 'k', 'M', 'G', 'T'];
        $suffixKey = (int) floor($base);
        $suffix    = $suffixes[$suffixKey];

        $number = round(pow(1024, $base - floor($base)), $precision);

        if ($format) {
            return '<span class="value">' . $number . '</span> <span class="measure">' . $suffix . '</span>';
        } else {
            return $number . ' ' . $suffix;
        }
    }

    /**
     * @param string $value
     * @param bool|string $filterName
     *
     * @return mixed
     */
    public function baseFilter($value, $filterName = false)
    {
        if (!$filterName) {
            return $value;
        }

        return $this->$filterName($value);
    }

}
