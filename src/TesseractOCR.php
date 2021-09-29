<?php

/*
 * This file is part of the OCR package.
 *
 * (c) Mark Fluehmann dbiz.apps@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\OCR;

use timatanga\OCR\Contracts\OcrEngine;
use timatanga\OCR\Exceptions\ImageNotFoundException;
use timatanga\OCR\Exceptions\OptionException;

class TesseractOCR implements OcrEngine
{
    /**
     * @var string
     */
    protected $executable = 'tesseract';

    /**
     * @var string|stdin
     */
    protected $file;

    /**
     * @var string|stdout
     */
    protected $output;

    /**
     * @var int
     */
    protected $dpi = 300;

    /**
     * @var int
     */
    protected $psm = 3;

    /**
     * @var int
     */
    protected $oem = 3;

    /**
     * @var string
     */
    protected $tessdataDir = null;

    /**
     * @var string
     */
    protected $userWords = null;

    /**
     * @var string
     */
    protected $userPattern = null;

    /**
     * @var string
     */
    protected $languages = 'eng';

    /**
     * @var array
     */
    protected $configFile = ['txt'];

    /**
     * @var array
     */
    protected $supportedConfig = ['txt', 'pdf', 'hocr', 'alto'];


    /**
     * Create Recognition Instance
     * 
     * @param array  $config
     */
    public function __construct( array $config = [])
    {
        if (! empty($config) )
            $this->parseConfig($config);
    }


    /**
     * Get Config
     * 
     * Combine all configurable options before calling the executable
     *
     * @return array
     */
    public function getConfig()
    {
        $options = [];

        // file, image
        $options[] = $this->file ?? '-';

        // output
        $options[] = $this->output ?? '-';

        // dpi
        $options[] = "--dpi $this->dpi";

        // psm
        $options[] = "--psm $this->psm";

        // oem
        $options[] = "--oem $this->oem";

        // language
        $options[] = '-l ' . $this->languages;

        // tessdata-dir
        if (! is_null($this->tessdataDir) )
            $options[] = '--tessdata-dir ' . $this->tessdataDir;

        // --user-patterns
        if (! is_null($this->userPattern) )
            $options[] = '--user-patterns ' . $this->userPattern;

        // --user-words
        if (! is_null($this->userWords) )
            $options[] = '--user-words ' . $this->userWords;

        // configfile 
        $options[] = implode(' ', $this->configFile);

        return $options;
    }


    /**
     * Set OCR Engine configuration
     * 
     * @param array  $config
     * @return mixed|exception
     */
    public function setConfig( array $config = [] )
    {
        $this->parseConfig($config);
    }


    /**
     * Run OCR Scan
     * 
     * @param array  $config
     * @return mixed|exception
     */
    public function scan( array $config = [] )
    {
        try {

            if ( empty($config) )
                $config = $this->getConfig();

            $process = new Process($this->executable, $config);

            return $process->execute();
            
        } catch (BinaryNotFoundException $e) {

            throw new ProcessFailedException('Error while processing the ocr scan: ' . $e->getMessage());

        }
    }


    /**
     * Parse Config
     * 
     * Parse and split config to set options
     *
     * @param string  $file        path to file or stdin or -
     * @return $this
     */
    private function parseConfig( array $config = [] )
    {
        if ( isset($config['file']) )
            $this->setFile($config['file']);

        if ( isset($config['image']) )
            $this->setFile($config['image']);

        if ( isset($config['output']) )
            $this->setOutput($config['output']);   
            
        if ( isset($config['dpi']) )
            $this->setDpi($config['dpi']);

        if ( isset($config['oem']) )
            $this->setOem($config['oem']);

        if ( isset($config['psm']) )
            $this->setPsm($config['psm']);

        if ( isset($config['languages']) )
            $this->setLanguages($config['languages']);

        if ( isset($config['tessdataDir']) )
            $this->setTessdataDir($config['tessdataDir']);

        if ( isset($config['userPatterns']) )
            $this->setUserPatterns($config['userPatterns']);

        if ( isset($config['userWords']) )
            $this->setUserWords($config['userWords']);

        if ( isset($config['configFile']) )
            $this->setConfigFile($config['configFile']);  
    }


    /**
     * Set Input File
     * 
     * The name of the input file. This can either be an image file or a text file.
     * A text file lists the names of all input images (one image name per line).
     * If FILE is stdin or - then the standard input is used.
     *
     * @param string  $file        path to file or stdin or -
     * @return void
     */
    public function setFile( string $file )
    {
        $this->file = $this->validateFile($file);
    }


    /**
     * Set Image
     * 
     * Alias for set input
     *
     * @param string  $file        path to file or stdin or -
     * @return void
     */
    public function setImage( string $file )
    {
        $this->setFile($file);
    }


    /**
     * Set Output Base
     * 
     * The basename of the output file (to which the appropriate extension will be appended). 
     * By default the output will be a text file with .txt added to the basename unless 
     * there are one or more parameters set which explicitly specify the desired output.
     * If OUTPUTBASE is stdout or - then the standard output is used.
     *
     * @param string  $output        path to file or stdout or -
     * @return void
     */
    public function setOutput( string $output )
    {
        $this->output = $output;
    }


    /**
     * Set DPI
     * 
     * Specify the resolution N in DPI for the input image(s). A typical value for N is 300. 
     * Without this option, the resolution is read from the metadata included in the image. 
     * If an image does not include that information, Tesseract tries to guess it.
     *
     * @param int  $dpi
     * @return void
     */
    private function setDpi( int $dpi )
    {
        if ( $dpi < 80 || $dpi > 600 )
            throw new OptionException('DPI lower than 80 or higher than 600 are not supported.');

        $this->dpi = $dpi;
    }


    /**
     * Set Psm
     * 
     * Page segmentation method (psm). By default Tesseract expects a page of text when it segments an image. 
     * If you’re just seeking to OCR a small region, try a different segmentation mode, using the --psm argument.
     *
     * Options are:
     *  0 => 'Orientation and script detection (OSD) only.',
     *   1 => 'Automatic page segmentation with OSD.',
     *   2 => 'Automatic page segmentation, but no OSD, or OCR.',
     *   3 => 'Fully automatic page segmentation, but no OSD. (Default)',
     *   4 => 'Assume a single column of text of variable sizes.',
     *   5 => 'Assume a single uniform block of vertically aligned text.',
     *   6 => 'Assume a single uniform block of text.',
     *   7 => 'Treat the image as a single text line.',
     *   8 => 'Treat the image as a single word.',
     *   9 => 'Treat the image as a single word in a circle.',
     *   10 => 'Treat the image as a single character.',
     *   11 => 'Sparse text. Find as much text as possible in no particular order.',
     *   12 => 'Sparse text with OSD.',
     *   13 => 'Raw line. Treat the image as a single text line, bypassing hacks that are Tesseract-specific.',
     * 
     * @param int  $psm
     * @return void
     */
    private function setPsm( int $psm )
    {
        if ( $psm < 0 || $psm > 13 )
            throw new OptionException('PSM modes range from 0 .. 13. Please choose different mode: ' .$psm);

        $this->psm = $psm;
    }


    /**
     * Set Oem
     * 
     * Specify OCR Engine mode.
     *
     * Options are:
     *  0 => 'Original Tesseract only.',
     *   1 => 'Neural nets LSTM only.',
     *   2 => 'Tesseract + LSTM.',
     *   3 => 'Default, based on what is available.', 
     * 
     * @param int  $psm
     * @return void
     */
    private function setOem( int $oem )
    {
        if ( $oem < 0 || $oem > 3 )
            throw new OptionException('PSM modes range from 0 .. 3. Please choose different mode: ' .$oem);

        $this->oem = $oem;
    }


    /**
     * Set Language
     * 
     * The language or script to use. If none is specified, eng (English) is assumed. 
     * Multiple languages may be specified, separated by plus characters. 
     * Tesseract uses 3-character ISO 639-2 language code
     *
     * @param array  $lang
     * @return void
     */
    public function setLanguages( array $lang )
    {
        $supportedLang = $this->supportedLanguages($lang);

        $existingLang = explode('+', $this->languages);

        $lang = array_unique(array_merge($existingLang, $supportedLang));

        $this->languages = implode('+', $lang);
    }


    /**
     * Set Tesseract Dir
     * 
     * Specify the location of tessdata path.
     * 
     * @param string  $path
     * @return void
     */
    private function setTessdataDir( string $path )
    {
        if (! is_dir($path) )
            throw new OptionException('Could not locate the directory for tessdata: ' .$path);

        $this->tessdataDir = $path;
    }


    /**
     * Set User Patterns
     * 
     * Specify the location of user patterns file.
     * 
     * @param string  $path
     * @return void
     */
    private function setUserPatterns( string $path )
    {
        $this->userPattern = $this->validateFile($path);
    }


    /**
     * Set User Words
     * 
     * Specify the location of user words file.
     * 
     * @param string  $path
     * @return void
     */
    private function setUserWords( string $path )
    {
        $this->userWords = $this->validateFile($path);
    }


    /**
     * Set Configfile
     * 
     * The name of a config to use. 
     * 
     * Supported options are:
     * - alto       Output in ALTO format (OUTPUTBASE.xml).
     * - hocr       Output in hOCR format (OUTPUTBASE.hocr).
     * - pdf        Output PDF (OUTPUTBASE.pdf).
     * - txt        Output plain text (OUTPUTBASE.txt).
     *
     * @param string  $path
     * @return void
     */
    private function setConfigFile( array $options )
    {
        if (! array_diff_key(array_flip($options), $this->supportedConfig) )
            throw new OptionException('Given config patterns are not supported: ' .$options);

        $this->configFile = $options;
    }


    /**
     * Supported languages
     *
     * @param array $lang   custom languages to test against supported languages
     * @return array
     */
    public function supportedLanguages( array $lang = [] )
    {
        // get supported languages
        $supportedLang = $this->getLanguages();

        // cast languages to array
        $lang = is_array($lang) ? $lang : [$lang];

        // reduce to available languages
        return array_intersect($lang, $supportedLang);
    }


    /**
     * Validate existance of file
     * 
     * @param string  $file
     * @return string
     */
    private function validateFile( string $file )
    {
        if (! file_exists($file) )
            throw new ImageNotFoundException('Could not locate file: ' . $file);

        if ( mime_content_type($file) != 'text/plain' )
            return $file;

        // read the file line by line into an array 
        $txtArray = file($file);

        // check file existance for every line
        foreach($txtArray as $key => $line) {

            $f = explode(" ", $line);

            if (! file_exists($f) )
                throw new ImageNotFoundException('Could not locate file: ' . $f);
        }

        return file;
    }



    /**
     * Get Tesseract Version
     * 
     * @return string
     */
    public function getVersion()
    {
        try {

            $process = new Process($this->executable, ['--version']);

            return $process->execute();            

        } catch (BinaryNotFoundException $e) {
            throw new BinaryNotFoundException( $e->getMessage() );

        } catch (ProcessFailedException $e) {
            throw new ProcessFailedException( $e->getMessage() );

        }
    }


    /**
     * Get Supported Languages
     * 
     * @return array
     */
    public function getLanguages()
    {
        try {

            $process = new Process($this->executable, ['--list-langs']);

            $languages = $process->execute();  

            return explode(PHP_EOL, $languages);          

        } catch (BinaryNotFoundException $e) {
            throw new BinaryNotFoundException( $e->getMessage() );

        } catch (ProcessFailedException $e) {
            throw new ProcessFailedException( $e->getMessage() );

        }
    }


    /**
     * Magic call method
     * 
     * Instead of providing a publicly accessible method for each config option
     * the call method acts as interface for class usage
     *
     * @param string  $name        method name
     * @param array  $arguments    method arguments
     * @return void
     */
    public function __call( string $name , array $arguments = [] )
    {
        $method = 'set' . ucfirst($name);

        if ( count($arguments) == 1 )
            $arguments = $arguments[0];

        if ( method_exists($this, $method) )
            call_user_func($method, $arguments);
    }


    /**
     * Access config options
     *
     * @param string  $name 
     * @return void
     */
    public function __get( string $name )
    {
        if (! property_exists($this, $name) )
            throw new OptionException('Requested option is not available: ' .$name);

        return $this->{$name};
    }
}
