<?php

namespace EPWT\XhprofBundle\Core;

/**
 * Class XhprofHandler
 * @package EPWT\XhprofBundle\Core
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class XhprofHandler
{
    /**
     * @var bool
     */
    protected $isProfiling;

    /**
     * @var array
     */
    protected $runs;

    /**
     * @var bool
     */
    protected $skipBuiltInFunctions;

    /**
     * @var
     */
    protected $currentRunName;

    public function __construct()
    {
        $this->isProfiling = false;
        $this->skipBuiltInFunctions = false;
        $this->runs = [];
    }

    /**
     * @return boolean
     */
    public function isSkipBuiltInFunctions()
    {
        return $this->skipBuiltInFunctions;
    }

    /**
     * @param boolean $skipBuiltInFunctions
     *
     * @return $this
     */
    public function setSkipBuiltInFunctions($skipBuiltInFunctions)
    {
        $this->skipBuiltInFunctions = $skipBuiltInFunctions;

        return $this;
    }

    /**
     * Calculate the flags for xhprof_enable
     *
     * @return int
     */
    public function getFlags()
    {
        $flags = XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY;
        if ($this->isSkipBuiltInFunctions()) {
            $flags |= XHPROF_FLAGS_NO_BUILTINS;
        }

        return $flags;
    }

    /**
     * @return boolean
     */
    public function isProfiling()
    {
        return $this->isProfiling;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function startProfiling($name = null)
    {
        $this->currentRunName = $name;
        $this->isProfiling = true;

        return true;
    }

    public function stopProfiling($xhprofData)
    {
        if (null === $xhprofData) {
            return false;
        }

        $runId = uniqid();
        $currentRunName = $this->currentRunName;

        if (!isset($this->runs[$currentRunName])) {
            $this->runs[$currentRunName] = [];
        }

        $microTime = microtime(true);
        $time = sprintf("%06d",($microTime - floor($microTime)) * 1000000);
        $time = new \DateTime( date('Y-m-d H:i:s.'.$time, $microTime) );

        $this->runs[$currentRunName][$runId] = [
            'time' => $time,
            'data' => $xhprofData
        ];

        $this->isProfiling = false;

        return true;
    }

    public function collectData()
    {
        if (true === $this->isProfiling) {
            $xhprofData = xhprof_disable();
            $this->stopProfiling($xhprofData);
        }

        return $this->runs;
    }
}
