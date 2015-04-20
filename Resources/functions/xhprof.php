<?php

use EPWT\XhprofBundle\Core\GlobalFunction;

if (!function_exists('xhprofStart')) {

    /**
     * @author Aurimas Niekis <aurimas.niekis@gmail.com>
     */
    function xhprofStart($name = null)
    {
        GlobalFunction::starProfiling($name);

        xhprof_enable(GlobalFunction::getFlags());
    }
}

if (!function_exists('xhprofEnd')) {

    /**
     * @author Aurimas Niekis <aurimas.niekis@gmail.com>
     */
    function xhprofEnd()
    {
        $xhprofData = xhprof_disable();

        GlobalFunction::endProfiling($xhprofData);
    }
}
