<?php

namespace EPWT\XhprofBundle;

use EPWT\XhprofBundle\Core\GlobalFunction;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EPWTXhprofBundle extends Bundle
{
    public function boot()
    {
        if ($this->container->getParameter('kernel.debug') || extension_loaded('xhprof')) {
            $handler = $this->container->get('epwt_xhprof.xhprof.handler');

            GlobalFunction::setHandler($handler);
        }
    }
}
