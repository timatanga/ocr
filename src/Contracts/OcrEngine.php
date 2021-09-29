<?php

/*
 * This file is part of the OCR package.
 *
 * (c) Mark Fluehmann dbiz.apps@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\OCR\Contracts;

interface OcrEngine 
{

    /**
     * Set OCR Engine configuration
     * 
     * @param array  $config
     * @return mixed|exception
     */
    public function setConfig( array $config = [] );

    /**
     * Get Config
     * 
     * Combine all configurable options before calling the executable
     *
     * @return array
     */
    public function getConfig();

    /**
     * Set Image
     * 
     * Alias for set input
     *
     * @param string  $file        path to file or stdin or -
     * @return void
     */
    public function setImage( string $file );

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
    public function setOutput( string $output );

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
    public function setLanguages( array $lang );

    /**
     * Run OCR Scan
     * 
     * @param array  $config
     * @return mixed|exception
     */
    public function scan( array $config = [] );

    /**
     * Get Tesseract Version
     * 
     * @return string
     */
    public function getVersion();

    /**
     * Get Supported Languages
     * 
     * @return string
     */
    public function getLanguages();

    /**
     * Magic call method
     * 
     * Instead of providing a public accessible method for each config option
     * the call method acts as wrapper to set engine specific options
     *
     * @param string  $name        method name
     * @param array  $arguments    method arguments
     * @return void
     */
    public function __call( string $name , array $arguments = [] );

    /**
     * Access config options
     *
     * @param string  $name 
     * @return void
     */
    public function __get( string $name );
}
