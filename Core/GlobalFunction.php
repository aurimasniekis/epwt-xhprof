<?php

namespace EPWT\XhprofBundle\Core;

require_once __DIR__ . '/../Resources/functions/xhprof.php';

/**
 * Class GlobalFunction
 * @package EPWT\XhprofBundle\Core
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class GlobalFunction
{
    /**
     * @var XhprofHandler
     */
    private static $handler;

    /**
     * @param string $name Run name
     *
     * @return bool|mixed
     */
    public static function starProfiling($name = null)
    {
        if (null !== self::$handler) {
            return self::$handler->startProfiling($name);
        }

        return false;
    }

    /**
     * @return bool|mixed
     */
    public static function endProfiling($xhprofData)
    {
        if (null !== self::$handler) {
            return self::$handler->stopProfiling($xhprofData);
        }

        return false;
    }

    /**
     * @return bool|mixed
     */
    public static function getFlags()
    {
        if (null !== self::$handler) {
            return self::$handler->getFlags();
        }

        return false;
    }

    /**
     * @param XhprofHandler
     */
    public static function setHandler($handler)
    {
        self::$handler = $handler;
    }
}
