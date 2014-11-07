<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  12/10/12 13:50
 */

namespace  Parallelizer\Tests;

use Parallelizer\Parallelizer;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class ParallelizerTest extends \PHPUnit_Framework_TestCase
{

    protected function getProcess($msg)
    {
        return new PhpProcessUsable(<<<PHP
<?php usleep(rand(0, 20000)); echo '$msg';
PHP
        );
    }

    public function processProvider()
    {
        return array(
            'single run with no process' => array(0, 1),
            'single run with 1 process' => array(1, 1),
            'single run with 2 processes' => array(2, 1),
            'single run with 10 processes' => array(10, 1),
            'dual run with no process' => array(0, 2),
            'dual run with 1 process' => array(1, 2),
            'dual run with 2 processes' => array(2, 2),
            'dual run with 10 processes' => array(10, 2),
            'quad run with no process' => array(0, 2),
            'quad run with 1 process' => array(1, 4),
            'quad run with 2 processes' => array(2, 4),
            'quad run with 10 processes' => array(10, 4),
        );
    }

    /**
     * @dataProvider processProvider
     */
    public function testRun($processes, $max)
    {
        $parallelizer = new Parallelizer($max);
        $expected = array();

        for ($i=0; $i<$processes; $i++) {
            $msg = sprintf('process#%d|', $i);
            $process = $this->getProcess($msg);
            $parallelizer->add($process);
            $expected[] = $msg;
        }

        $tested = array();
        $parallelizer->run(function ($level, $output) use (&$tested) {
                $tested[] = $output;
        });

        sort($tested);
        sort($expected);

        $this->assertEquals($expected, $tested);
    }
}

class PhpProcessUsable extends PhpProcess
{
    public function start($callback = null)
    {
        $executableFinder = new PhpExecutableFinder();
        if (null === $this->getCommandLine()) {
            if (false === $php = $executableFinder->find()) {
                throw new \RuntimeException('Unable to find the PHP executable.');
            }
            $this->setCommandLine($php);
        }

        return parent::start($callback);
    }
}
