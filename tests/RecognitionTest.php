<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use timatanga\OCR\Recognition;

class RecognitionTest extends TestCase
{

    public function test_get_supported_languages()
    {
        $engine = new Recognition;

        $lang = $engine->supportedLanguages();

        $this->assertTrue( in_array('eng', $lang) );
        $this->assertTrue( in_array('deu', $lang) );
        $this->assertTrue( in_array('fra', $lang) );
    }


    public function test_get_engine_version()
    {
        $engine = new Recognition;

        $version = $engine->version();

        $this->assertTrue( strpos($version, 'tesseract') !== false );
    }


    public function test_create_engine_instance()
    {
        $pwd = __DIR__;

        $engine = new Recognition($pwd.DIRECTORY_SEPARATOR.'sample.png', $pwd, 'test');

        $this->assertTrue( $engine->getImage() == $pwd.DIRECTORY_SEPARATOR.'sample.png');
        $this->assertTrue( $engine->getOutputDir() == $pwd);
    }


    public function test_create_engine_instance_with_late_image()
    {
        $pwd = __DIR__;

        $engine = new Recognition(null, $pwd, 'test');

        $engine->setImage($pwd.DIRECTORY_SEPARATOR.'sample.png');

        $this->assertTrue( $engine->getImage() == $pwd.DIRECTORY_SEPARATOR.'sample.png');
        $this->assertTrue( $engine->getOutputDir() == $pwd);
    }


    public function test_set_language()
    {
        $pwd = __DIR__;

        $engine = new Recognition();

        $engine->setLanguages(['deu', 'eng']);

        $this->assertTrue( $engine->getLanguages() == ['eng', 'deu']);
    }


    public function test_set_config()
    {
        $pwd = __DIR__;

        $engine = new Recognition();

        $engine->setConfig(['oem' => 2]);
        $engine->setConfig(['psm' => 10]);
        $engine->setConfig(['languages' => ['eng', 'fra']]);
        $engine->setConfig(['configFile' => ['pdf']]);

        $this->assertTrue( $engine->getConfig('oem') == 2);
        $this->assertTrue( $engine->getConfig('psm') == 10);
        $this->assertTrue( $engine->getConfig('languages') == 'eng+fra');
        $this->assertTrue( $engine->getConfig('configFile') == ['pdf']);
    }


    public function test_scan_image_streamed()
    {
        $pwd = __DIR__;
        $file = $pwd.DIRECTORY_SEPARATOR.'sample.png';

        $engine = new Recognition($file);

        $this->assertTrue( strlen($engine->scan()) > 10 );
    }


    public function test_scan_image_file()
    {
        $pwd = __DIR__;
        $file = $pwd.DIRECTORY_SEPARATOR.'sample.png';

        // $engine = new Recognition($pwd.DIRECTORY_SEPARATOR.'sample.png', $pwd, 'output');
        $engine = new Recognition($file, $pwd, 'output');

        $engine->scan();

        $this->assertTrue( strlen(file_get_contents($pwd.DIRECTORY_SEPARATOR.'output.txt')) > 10 );

        unlink($pwd.DIRECTORY_SEPARATOR.'output.txt');
    }


    public function test_scan_image_file_as_pdf()
    {
        $pwd = __DIR__;
        $file = $pwd.DIRECTORY_SEPARATOR.'sample.png';

        // $engine = new Recognition($pwd.DIRECTORY_SEPARATOR.'sample.png', $pwd, 'output');
        $engine = new Recognition($file, $pwd, 'output');
        $engine->setConfig(['configFile' => ['pdf']]);

        $engine->scan();

        $this->assertTrue( strlen(file_get_contents($pwd.DIRECTORY_SEPARATOR.'output.pdf')) > 10 );

        unlink($pwd.DIRECTORY_SEPARATOR.'output.pdf');
    }


    public function test_scan_image_file_as_hocr()
    {
        $pwd = __DIR__;
        $file = $pwd.DIRECTORY_SEPARATOR.'sample.png';

        // $engine = new Recognition($pwd.DIRECTORY_SEPARATOR.'sample.png', $pwd, 'output');
        $engine = new Recognition($file, $pwd, 'output');
        $engine->setConfig(['configFile' => ['hocr']]);

        $engine->scan();

        $this->assertTrue( strlen(file_get_contents($pwd.DIRECTORY_SEPARATOR.'output.hocr')) > 10 );

        unlink($pwd.DIRECTORY_SEPARATOR.'output.hocr');
    }

}

