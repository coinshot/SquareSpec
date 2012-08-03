<?php
/**
 * @package SquareSpec
 * @author Benjie Velarde
 * @copyright (c) 2012, Benjie Velarde bvelarde@gmail.com
 * @lincense http://opensource.org/licenses/PHP-3.0
 */
namespace SquareSpec;
/**
 * The spec description 
 * --- proove horizontals :) , if you don't get it, nevermind ;)
 */
class SpecLevel implements Testable {
    /**
     * @var string The description text
     */
    private $desc;
    /**
     * @var array To stack children descriptions and contexts
     */    
    private $contexts;
    /**
     * @var array The test subjects
     */        
    private $subjects;
    /**
     * Constructor
     *
     * @param string $desc The spec description
     * @return SpecLevel
     */ 
    public function __construct($desc) {
        $this->desc     = $desc;
        $this->results  =
        $this->contexts =
        $this->subjects = array();
    }
    /**
     * The method to receive / wrap all the children descriptions and contexts
     *
     * @param mixed $v,... List of descriptions and contexts. The first param could be a call to Spec::before which should ultimately return an associative array containing the test subjects.
     * @return SpecLevel
     */
    public function &spec() {
        $args = func_get_args();
        if (is_array($args[0])) {
            $before = array_shift($args);
            $this->subjects = $this->wrapSubjects($before);
        }
        foreach ($args as $arg) {
            if ($arg instanceof Testable) {
                $arg->addSubjects($this->subjects);
                $this->contexts[$arg->getDescription()] = $arg;
            }
        }
        return $this;
    }
    /**
     * Use to pass subjects to children contexts and descriptions
     *
     * @param array
     */
    public function addSubjects(array $subjects) {
        $subjects = $this->wrapSubjects($subjects);
        $this->subjects = array_merge($subjects, $this->subjects);
        foreach ($this->contexts as $desc => $context) {
            $context->addSubjects($subjects);
            $this->contexts[$desc] = $context;
        }
    }
    /**
     * Get the description
     *
     * @return string
     */    
    public function getDescription() { return $this->desc; }
    /**
     * Run all test, store results for further evaluation
     *
     * @return array
     */
    public function test() {
        foreach ($this->contexts as $desc => $context) {
            $this->results[$desc] = $context->test();
        }
        return $this->results;
    }
    /**
     * Evaluates results after a call to SpecLevel::test
     *
     * @return array
     */
    public function evaluate($results=NULL, $parent_data=array()) {
        $results = $results ? $results : $this->results;
        if ($parent_data) {
            list($total, $success, $failures) = $parent_data;
        } else {
            $total = $success = 0;
            $failures = array();
        }
        foreach ($results as $desc => $result) {           
            if (is_array($result)) {
                return $this->evaluate($result, array($total, $success, $failures));
            } else {
                if ($result) {
                    $success++;
                    echo '.';
                } else {
                    $failures[] = $desc;
                    echo 'F';
                }
                $total++;
            }
        }
        return array($total, $success, $failures);
    }
    /**
     * Run the test and evaluate, echo the results. NOTE: use only on the outer-most description layer. Do not call this on children contexts and descriptions
     */
    public function run() {
        $this->test();
        list($total, $success, $failures) = $this->evaluate();
        echo "<br/>";
        if ($failures) {
            echo "Failures:<br/> -" . implode("<br/> -", $failures);
            echo "<br/>";
        }
        echo "Success: $success<br/>";
        echo "Total: $total";
    }
    /**
     * Wrap each subjects as SpecSubject objects
     *
     * @return array
     */
    private function wrapSubjects(array $subjects) {
        foreach ($subjects as $k => $subject) {
            $subjects[$k] = new SpecSubject($subject);
        }
        return $subjects;
    }
}
?>