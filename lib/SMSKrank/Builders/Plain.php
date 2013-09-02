<?php

namespace SMSKrank\Builders;

use SMSKrank\Interfaces\MessageBuilderInterface;

class Plain implements MessageBuilderInterface
{
    // "some {thing}" + [thing => 'foo'] => "some foo"
    // "some{?thing}: {thing}{thing?} + [thing => 'foo'] => "some: thing"
    // "some{?thing}: {thing}{thing?} + [] => "some"
    // "some{!thing}: no things{thing!} + [] => "some: no things"
    public function build($pattern, array $arguments)
    {
        // check for conditions

        $conditions = $this->getConditions($pattern);

        if (empty($conditions)) {
            $pattern = $this->replacePlaceholders($pattern, $arguments);
        } else {

            $compiled  = array();
            $last_good = 0;

            foreach ($conditions as $c) {
                $replacement = '';

                // 1. Check condition
                // ? - if exists and not empty
                // ! - if doesn't exists or empty

                if ($c['condition'] == '?') {
                    if (!empty($arguments[$c['placeholder']])) {
                        $replacement = $this->build($c['pattern'], $arguments);
                    }
                } elseif ($c['condition'] == '!') {
                    if (empty($arguments[$c['placeholder']])) {
                        $replacement = $this->build($c['pattern'], $arguments);
                    }
                } else {
                    // should never get here, but if we do, replace whole condition with empty string
                }

                // 2. Run replacement itself

                $compiled[] = substr($pattern, $last_good, $c['starts'] - $last_good);
                $compiled[] = $replacement;

                $last_good = $c['starts'] + $c['length'];
            }

            $compiled[] = substr($pattern, $last_good);

            $pattern = implode($compiled);
        }

        return $pattern;
    }


    protected function getConditions($pattern)
    {
        $regex = '/\{([!?])(\w+)\}(.*)\{\g{2}+\g{1}\}/sU';

        $matches = array();

        $res = preg_match_all($regex, $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        if (!$res) {
            return $matches;
        }

        $out = array();

        foreach ($matches as $m) {
            $out[] = array(
                'starts'      => $m[0][1],
                'length'      => strlen($m[0][0]),
                'condition'   => $m[1][0],
                'placeholder' => $m[2][0],
                'pattern'     => $m[3][0]
            );
        }

        return $out;
    }

    protected function replacePlaceholders($pattern, array $arguments)
    {
        // get list of all placeholders, non-existent will be simply replaced with empty string
        $regex = '/\{(\w+)+\}/';

        $res = preg_match_all($regex, $pattern, $matches);

        if ($res) {
            $placeholders = array_unique($matches[1]);

            foreach ($placeholders as $name) {
                $pattern = str_replace('{' . $name . '}', isset($arguments[$name]) ? $arguments[$name] : '', $pattern);
            }
        }

        return $pattern;
    }

}