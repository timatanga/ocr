# Optical Character Recognition (OCR)

This package is a wrapper around the the tesseract-ocr (https://github.com/tesseract-ocr/tesseract) package.
This library depends on 
- Symfony Process
- Tesseract OCR, version 4 or later.



## Installation

composer require dbizapps/ocr



## Dependencies

There are many options to install Tesseract OCR according to your opeerating system needs.
Personal experiences are limited to macOS for which you can find a recommendation below. Sorry for this inconveniences.


### macOS

To install Tesseract OCR with support just for english

	$ brew install tesseract

For a fully fledged language installation you can choose

	$ brew install tesseract tesseract-lang



## Basic Usage

Creating a recognition engine instance doesn't require further attributes

	// create ocr instance
	$engine = new Recognition;


Retrieving tesseract specific information

	// retrieve tesseract version
	$version = $engine->version();

	// retrieve supported languages
    $lang = $engine->supportedLanguages();



## Input/Output Configuration

The target image can be set while creating a new recognition instance or at a later state

    $engine = new Recognition(<image-path>);

    $engine->setImage(<image-path>);


The engine can act in file mode to store results in a given output file and output path
Without providing output configuration options the later scan process is streaming results

    $engine = new Recognition(<image-path>, <output-dir>, <output-filename>);



## Custom Configuration

To get an overview of Tesseract OCR configuration options, please refer to https://github.com/tesseract-ocr/tesseract

Configuration options can be set individually like

	// Scan density
    $engine->setConfig(['dpi' => 300]);

	// OCR Engine mode.
    $engine->setConfig(['oem' => 2]);

    // Page segmentation method
    $engine->setConfig(['psm' => 10]);

    // Languages 
    $engine->setConfig(['languages' => ['eng', 'fra']]);

    // output configuration
    $engine->setConfig(['configFile' => ['pdf']]);


 Supported options for configFile are:
 - alto       Output in ALTO format (OUTPUTBASE.xml).
 - hocr       Output in hOCR format (OUTPUTBASE.hocr).
 - pdf        Output PDF (OUTPUTBASE.pdf).
 - txt        Output plain text (OUTPUTBASE.txt / default).


Instead of setting options individually there is a global settings method

	$engine->setConfig([
		'dpi' => 300,
		'oem' => 2,
		'psm' => 10,
		'languages' => ['eng', 'fra'],
		'configFile' => ['pdf'],
	])



## Scan usage

Scanning an image as simple as

	$result = $engine->scan()


If the output-path and output-filename are set, the provided result represents the path to the scaned output.
Otherwise the result respresents the scaned output.
