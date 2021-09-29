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

class Recognition {

    /**
     * @var OcrEngine
     */
    protected $engine;

    /**
     * @var string
     */
    protected $mode = 'stream';

    /**
     * @var string
     */
    protected $output = 'output';

    /**
     * @var array
     */
    protected $defaultLang = ['deu', 'fra', 'ita', 'eng'];

    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @var string
     */
    protected $outputDir;


    /**
     * Create Recognition Instance
     * 
     * @param string  $image        path to image fila
     * @param string  $outputDir    path to output directory
     * @param string  $file         output file name, if none the output is streamed
     */
    public function __construct( string $image = null, $outputDir = null, $file = null )
    {
        $this->engine = new TesseractOCR();

        $this->mode = !$file ? 'stream' : 'file';

        if (! is_null($image) )
            $this->engine->setImage($image);

        // set temporary directory
        $this->tmpDir = sys_get_temp_dir();

        // default output directory
        $this->outputDir = is_dir($outputDir) ? $outputDir : $this->setOutputDir();

        // set output file or stream mode
        $output = !$file ? '-' : $this->outputDir . DIRECTORY_SEPARATOR . $file;

        // set engine config
        $this->engine->setOutput($output);
    }


    /**
     * Set target image
     *
     * @param string  $image        path to image fila
     * @return $this
     */
    public function setImage( string $image )
    {
        $this->engine->setImage($image);

        return $this;
    }

    
    /**
     * Get target image
     * 
     * @return string
     */
    public function getImage()
    {
        return $this->engine->file;
    }


    /**
     * Set target image
     *
     * @param array  $languages
     * @return $this
     */
    public function setLanguages( array $languages )
    {
        $this->engine->setLanguages($languages);

        return $this;
    }


    /**
     * Pass config options to OCR engine
     *
     * @param array  $config
     * @return $this
     */
    public function setConfig( array $config )
    {
        $this->engine->setConfig($config);

        return $this;
    }


    /**
     * Get OCR Engine config for given option or all
     *
     * @param string $option
     * @return array
     */
    public function getConfig( string $option = null )
    {
        if (! is_null($option) )
            return $this->engine->{$option};

        return $this->engine->getConfig($option);
    }


    /**
     * Get Output Directory
     * 
     * @return string
     */
    private function setOutputDir()
    {
        if ( function_exists('storage_path') )
            return storage_path('app/ocr');

        return $this->tmpDir;
    }


    /**
     * Get Output Directory
     * 
     * @return string
     */
    public function getOutputDir()
    {
        return $this->outputDir;
    }


    /**
     * Get OCR Engine version
     *
     * @return string
     */
    public function getLanguages()
    {
        $lang = $this->engine->languages;

        if ( strlen($lang) == 0 )
            return null;

        return explode('+', $lang);
    }


    /**
     * Run OCR scan process
     *
     * @param string $filename
     * @return mixed
     */
    public function scan( string $filename = null )
    {
        if ( is_null($filename) )
            $filename = $this->output;

        // build path for output
        $location = $this->buildLocation($filename, 'txt');

        // get recognition results
        if ( $data = $this->engine->scan() ) {

            if ( $this->mode == 'stream' )
                return $data;

            // write scaned file
            file_put_contents($location, $data);

            return $location;
        }
    }


    /**
     * Supported languages
     * 
     * Retrieve supported languages by OCR engine
     *
     * @return array
     */
    public function supportedLanguages()
    {
        $languages = $this->engine->getLanguages();

        if (! $languages )
            return null;

        return $languages;
    }


    /**
     * Get OCR Engine version
     *
     * @return string
     */
    public function version()
    {
        return $this->engine->getVersion();
    }


    /**
     * Clean directory, except given file
     * 
     * @param string  $filename   file not to delete
     * @param bool    $include    inlude given file for deletion
     */ 
    public function cleanDirectory( $filename = null, $include = false )
    {
        // set directory
        $directory = dirname($filename);

        // List of name of files inside specified folder
        $files = glob($directory.'/*'); 

        // Deleting all the files in the list
        foreach($files as $file) {
           
            if( is_file($file) && ( $include || $file !== $filename ) )
            
                // Delete the given file
                unlink($file); 
        }
    }


    /**
     * Build unique location path
     * 
     * @param  string       $filename
     * @return string       $extension
     * @throws exception
     */
    private function buildLocation( string $filename, string $extension = null )
    {
        if (! isset($filename) )
            $filename = $this->uniqueName($extension);

        if ( !is_null($extension) && !strpos($filename, $extension) )
            $filename .= '.'. $extension;

        $location = $this->outputDir . DIRECTORY_SEPARATOR . $filename;

        $directoryPath = dirname($location);

        if (! is_dir($directoryPath))
            mkdir($directoryPath, 0777, true);

        return $location;
    }


   /**
    * Build unique filename with the given extension
    * 
    * @param string $extension (pdf / html / jpg )
    * @return string $filename
    */
    private function uniqueName( string $extension = '' )
    {
        return md5(date('Y-m-d H:i:s:u')) . '.' . $extension;
    }
}
