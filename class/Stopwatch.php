<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stopwatch
 *
 * @author GGCoke
 */
class Stopwatch {

    private $start;
    private $end;
    private $markup_start = array();
    private $markup_end = array();

    function __construct($markup=false) {
        $this->start($markup);
    }

    public function start($markup=false) {
        if (!$markup) {
            $this->start = $this->getmicrotime();
        } else {
            $this->markup_start[$markup] = $this->getmicrotime();
        }
    }

    public function stop($markup=false) {
        if (!$markup) {
            $this->end = $this->getmicrotime();
        } else {
            $this->markup_end[$markup] = $this->getmicrotime();
        }
        return $this->getDuration($markup);
    }

    public function getDuration($markup=false) {
        if (!$markup) {
            return number_format($this->end - $this->start, 4);
        } else {
            return number_format($this->markup_end[$markup] - $this->markup_start[$markup], 4);
        }
    }

    public function reset($markup) {
        if (!$markup) {
            $this->start = 0;
            $this->end = 0;
            $this->markup_start = array();
            $this->markup_end = array();
        } else {
            $this->markup_start[$markup] = 0;
            $this->markup_end[$markup] = 0;
        }
    }

    private function getmicrotime() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

}
