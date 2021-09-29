<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use timatanga\OCR\Exceptions\BinaryNotFoundException;
use timatanga\OCR\Process;

class ProcessTest extends TestCase
{

    public function testProcess()
    {
        $process = new Process('ls', ['-la']);

        $output = $process->execute();

        $this->assertTrue( strlen($output) > 0);
        $this->assertTrue( strpos($output, 'LICENSE') !== FALSE );
    }


    public function testProcessInvalidExecutable()
    {
        try {

            $process = new Process('bala');
    
            $output = $process->execute();
            
        } catch (BinaryNotFoundException $e) {

            $this->assertTrue( $e->getMessage() == 'Could not find binary: bala' );

        }
    }
    
}
