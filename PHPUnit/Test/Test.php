<?php

namespace PHPUnit\Test;

/**
 * Class Test
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2015-10-28
 * @project
 */
class Test extends \PHPUnit_Framework_TestCase {

    public function testTrueIsTrue() {
        $foo = true;
        $this->assertTrue($foo);
    }
}

?>
