<?php

namespace EPWT\XhprofBundle\Core;

/**
 * Class XhprofParser
 *
 * @package EPWT\XhprofBundle\Core
 * @author Aurimas Niekis <aurimas@mailerlite.com>
 */
class XhprofParser
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $groups;

    /**
     * @var array
     */
    protected $friendlyNameColors;

    /**
     * @var array
     */
    protected $internalFunctions;

    /**
     * @var Colors
     */
    protected $colors;

    /**
     * @var array
     */
    protected $typesData;

    /**
     * @var
     */
    protected $calculatedPossibleMetrics;

    public function __construct($data)
    {
        $this->colors = new Colors();
        $this->data = $data;
        $this->groups = [];
        $this->calculatedPossibleMetrics = [];
        $this->friendlyNameColors = [];
    }

    /**
     * @return array
     */
    public function getInternalFunctions()
    {
        if ($this->internalFunctions) {
            return $this->internalFunctions;
        }

        $this->internalFunctions = get_defined_functions();

        return $this->internalFunctions;
    }

    /**
     * @return array
     */
    public function getCalculatedPossibleMetrics()
    {
        $keys = array_flip($this->calculatedPossibleMetrics);

        return array_intersect_key($this->getPossibleMetrics(), $keys);
    }

    public function getGroupProviders($groups)
    {
        $providers = [];

        foreach ($groups as $group) {
            $providerName = $group['provider'];
            if (isset($providers[$providerName])) {
                $providers[$providerName]['value'] += $group['count'];
            } else {
                $provider = [];
                $provider['value'] = $group['count'];
                $provider['color'] = $group['color'];
                $provider['label'] = $group['provider'];

                $providers[$providerName] = $provider;
            }
        }

        return json_encode(array_values($providers));
    }

    public function getTypesData()
    {
        if ($this->typesData) {
            return $this->typesData;
        }

        $this->typesData = [];

        foreach ($this->data['runs'] as $name => $runs) {
            $firstRunData = reset($runs);

            $typeData = [];
            $typeData['name'] = $name;
            $typeData['id'] = str_replace(' ', '', 'sample' . ucfirst($name));
            $typeData['metrics'] = $this->getMetrics($firstRunData['data']);

            $this->calculatedPossibleMetrics = array_merge(
                $this->calculatedPossibleMetrics,
                array_keys($typeData['metrics'])
            );

            $typeData['runs'] = [];
            foreach ($runs as $runId => $data) {
                $runData = [];
                $runData['runId'] = $runId;
                $runData['id'] = str_replace(' ', '', 'sample' . ucfirst($name) . $runId);
                $runData['time'] = $data['time'];

                $flatData = $this->calculateRunCallStack($typeData, $data['data']);
                $runData = array_merge($runData, $flatData);

                $typeData['runs'][$runId] = $runData;
            }

            $typeData['stats'] = $this->getMainStats($typeData);

            $this->typesData[$name] = $typeData;
        }

        $this->calculatedPossibleMetrics = array_unique($this->calculatedPossibleMetrics);

        return $this->typesData;
    }

    /**
     * @param string $parentChild
     *
     * @return array
     */
    public function parseParentChild($parentChild)
    {
        $results = explode('==>', $parentChild);

        if (isset($results[1])) {
            return $results;
        }

        return [null, $results[0]];
    }

    /**
     * @return array
     */
    public function getPossibleMetrics()
    {
        return [
            'wt' => [
                'title' => 'Wall Time',
                'filter' => 'microSecondsFilter'
            ],
            'ut' => [
                'title' => 'User',
                'filter' => 'microSecondsFilter'
            ],
            'st' => [
                'title' => 'System',
                'filter' => 'microSecondsFilter'
            ],
            'cpu' => [
                'title' => 'CPU',
                'filter' => 'microSecondsFilter'
            ],
            'mu' => [
                'title' => 'Memory Usage',
                'filter' => 'bytesFilter'
            ],
            'pmu' => [
                'title' => 'Peak Memory Usage',
                'filter' => 'bytesFilter'
            ],
            'sample' => [
                'title' => 'Samples',
                'filter' => false
            ],
        ];
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    public function getMetrics($rawData)
    {
        $possibleMetrics = $this->getPossibleMetrics();

        $metrics = [];

        foreach ($possibleMetrics as $metric => $info) {
            if (isset($rawData['main()'][$metric])) {
                $metrics[$metric] = $info;
            }
        }

        return $metrics;
    }

    public function computeInclusiveTimes($rawData)
    {
        $functions = [];
        $metrics = array_keys($this->getMetrics($rawData));

        foreach ($rawData as $parentChild => $stats) {
            list($parent, $child) = $this->parseParentChild($parentChild);

            if ($parent == $child) {
                return [];
            }

            if (!isset($functions[$child])) {
                $function = [];
                $function['caller'] = $parent;
                $function['callee'] = $child;
                $function['symbol'] = $parentChild;
                $function['stats'] = [];

                foreach ($metrics as $metric) {
                    $function['stats'][$metric] = $stats[$metric];
                }

                $function['stats']['ct'] = $stats['ct'];

                $functions[$child] = $function;
            } else {
                foreach ($metrics as $metric) {
                    $functions[$child]['stats'][$metric] += $stats[$metric];
                }
            }
        }

        return $functions;
    }

    public function computeFlatInfo($typeData, $rawData)
    {
        $metrics = array_keys($typeData['metrics']);
        $functions = $this->computeInclusiveTimes($rawData);

        $overallTotals = [];
        foreach ($metrics as $metric) {
            $overallTotals[$metric] = $functions['main()']['stats'][$metric];
        }

        $overallTotals['ct'] = $functions['main()']['stats']['ct'];

        foreach ($functions as $function => $data) {
            foreach ($metrics as $metric) {
                $functions[$function]['stats']['excl_' . $metric] = $data['stats'][$metric];
            }

            $overallTotals['ct'] += $data['stats']['ct'];
        }

        foreach ($rawData as $parentChild => $stats) {
            list($parent, $child) = $this->parseParentChild($parentChild);

            if ($parent) {
                foreach ($metrics as $metric) {
                    if (isset($functions[$parent])) {
                        $functions[$parent]['stats']['excl_' . $metric] -= $stats[$metric];
                    }
                }
            }
        }

        return [
            'data' => array_values($functions),
            'stats' => $overallTotals
        ];
    }


    public function calculateRunCallStack($typeData, $rawData)
    {
        $runData = $this->computeFlatInfo($typeData, $rawData);

        $results = [];
        $results['callstack'] = [];

        $internalFunctions = $this->getInternalFunctions();

        $rootCall = null;
        foreach ($runData['data'] as $index => &$result) {
            $result['internal'] = in_array($result['callee'], $internalFunctions['internal']);
            $result['group'] = $this->getGroup($typeData['name'], $result);

            array_unshift($results['callstack'], $result);
        }

        $results['groups'] = $this->groups[$typeData['name']];
        $results['stats'] = $runData['stats'];

        return $results;
    }

    public function getMainStats($typeData)
    {
        $stats = [];
        $metrics = array_keys($typeData['metrics']);
        $metrics[] = 'ct';

        $first = true;
        foreach ($typeData['runs'] as $runData) {
            foreach ($metrics as $metric) {
                if ($first) {
                    $stats[$metric]['min'] = $runData['stats'][$metric];
                    $stats[$metric]['max'] = $runData['stats'][$metric];
                    $stats[$metric]['values'] = [$runData['stats'][$metric]];
                    continue;
                }

                if ($stats[$metric]['min'] > $runData['stats'][$metric]) {
                    $stats[$metric]['min'] = $runData['stats'][$metric];
                }

                if ($stats[$metric]['max'] < $runData['stats'][$metric]) {
                    $stats[$metric]['max'] = $runData['stats'][$metric];
                }

                $stats[$metric]['values'][] = $runData['stats'][$metric];
            }

            if ($first) {
                $first = false;
            }
        }

        foreach ($stats as $type => &$data) {
            $data['sum'] = array_sum(($data['values']));
            $data['avg'] = $data['sum'] / count($typeData['runs']);
            $data['percentile'] = $this->calculatePercentile($data['values'], 0.95);
            $data['occurrences'] = array_count_values($data['values']);
            $data['mode'] = array_search(max($data['occurrences']), $data['occurrences']);
        }

        return $stats;
    }

    protected function calculatePercentile($values, $percentile)
    {
        sort($values);
        $count = count($values);
        $allIndex = ($count - 1) * $percentile;
        $intValIndex = intval($allIndex);
        $floatVal = $allIndex - $intValIndex;

        if (!is_float($floatVal)) {
            $result = $values[$intValIndex];
        } else {
            if ($count > $intValIndex + 1) {
                $result = $floatVal * ($values[$intValIndex + 1] - $values[$intValIndex]) + $values[$intValIndex];
            } else {
                $result = $values[$intValIndex];
            }
        }

        return $result;
    }

    protected function getGroup($name, $data)
    {
        if (!$data['caller']) {
            return null;
        }

        if (!isset($data['internal'], $data['callee'])) {
            throw new \InvalidArgumentException('Data format does not comply with the XHProf conventions.');
        }

        if ($data['internal']) {
            $group = 'internal';
        } else {
            $group = explode('::', $data['callee'], 2);
            $group = count($group) === 1 ? 'user-defined function' : $group[0];
        }

        $namespaceParts = explode('\\', $group);

        $provider = null;
        if ($namespaceParts > 1) {
            $provider = $namespaceParts[0];
        }

        $friendlyName = $provider;
        if ($data['internal']) {
            $friendlyName = 'Internal Function';
        }

        if ($group == 'user-defined function') {
            $friendlyName = 'User Defined Function';
        }

        if (in_array($data['callee'], ['xhprofEnd', 'xhprof_disable'])) {
            $group = 'epwt_xhprof';
            $friendlyName = 'EPWT XHProf';
        }

        if (preg_match('/^Symfony\\\\Component\\\\([^\\\\]+)\\\\/', $group, $matches)) {
            $friendlyName = 'Symfony ' . $matches[1];
        }

        if (preg_match('/^Symfony\\\\Bridge\\\\([^\\\\]+)\\\\/', $group, $matches)) {
            $friendlyName = 'Symfony ' . $matches[1];
        }

        $bundleName = 'Not a bundle';
        if (preg_match('/\\\\([A-Za-z]+Bundle)\\\\/', $group, $matches)) {
            $bundleName = $matches[1];
            $friendlyName = $matches[1];
        }

        if (!isset($this->groups[$name])) {
            $this->groups[$name] = [];
        }

        if (isset($this->friendlyNameColors[$friendlyName])) {
            $color = $this->friendlyNameColors[$friendlyName];
        } else {
            $color = $this->colors->getColor();
            $this->friendlyNameColors[$friendlyName] = $color;
        }

        if (!isset($this->groups[$name][$group])) {
            $this->groups[$name][$group] = [
                'index' => count($this->groups) + 1,
                'name' => $group,
                'provider' => $provider,
                'friendlyName' => $friendlyName,
                'bundleName' => $bundleName,
                'color' => $color,
                'count' => 0
            ];
        }

        $this->groups[$name][$group]['count']++;

        return $this->groups[$name][$group];
    }
}
