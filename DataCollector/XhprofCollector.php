<?php

namespace EPWT\XhprofBundle\DataCollector;

use EPWT\XhprofBundle\Core\XhprofHandler;
use EPWT\XhprofBundle\Core\XhprofParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class XhprofCollector
 * @package EPWT\XhprofBundle\DataCollector
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class XhprofCollector extends DataCollector
{
    /**
     * @var XhprofHandler
     */
    protected $xhprofHandler;

    /**
     * @var XhprofParser
     */
    protected $xhprofParser;

    public function __construct()
    {
        $this->data                    = [];
        $this->data['extensionLoaded'] = extension_loaded('xhprof');
        $this->data['stats']           = [];
    }

    /**
     * @return XhprofParser
     */
    public function getXhprofParser()
    {
        if ($this->xhprofParser) {
            return $this->xhprofParser;
        }

        $this->xhprofParser = new XhprofParser($this->data);

        return $this->xhprofParser;
    }

    /**
     * @return XhprofHandler
     */
    public function getXhprofHandler()
    {
        return $this->xhprofHandler;
    }

    /**
     * @param XhprofHandler $xhprofHandler
     *
     * @return $this
     */
    public function setXhprofHandler($xhprofHandler)
    {
        $this->xhprofHandler = $xhprofHandler;

        return $this;
    }

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request $request A Request instance
     * @param Response $response A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['runs']     = $this->getXhprofHandler()->collectData();
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     *
     * @api
     */
    public function getName()
    {
        return 'epwt_xhprof';
    }

    public function getSessions()
    {
        $totalRuns = 0;

        foreach ($this->getRuns() as $runs) {
            $totalRuns += count($runs);
        }

        return $totalRuns;
    }

    public function getRuns()
    {
        return $this->data['runs'];
    }

    /**
     * @return boolean
     */
    public function isExtensionLoaded()
    {
        return $this->data['extensionLoaded'];
    }


}
