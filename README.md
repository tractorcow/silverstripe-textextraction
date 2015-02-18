# Text Extraction Module

[![Build Status](https://secure.travis-ci.org/silverstripe-labs/silverstripe-textextraction.png)](http://travis-ci.org/silverstripe-labs/silverstripe-textextraction)

## Overview

Provides an extraction API for file content, which can hook into different extractor
engines based on availability and the parsed file format.
The output is always a string: the file content.

Via the `FileTextExtractable` extension, this logic can be used to 
cache the extracted content on a `DataObject` subclass (usually `File`).

Note: Previously part of the [sphinx module](https://github.com/silverstripe/silverstripe-sphinx).

## Requirements

 * SilverStripe 3.1
 * (optional) [XPDF](http://www.foolabs.com/xpdf/) (`pdftotext` utility)
 * (optional) [Apache Solr with ExtracingRequestHandler](http://wiki.apache.org/solr/ExtractingRequestHandler)
 * (optional) [Apache Tika](http://tika.apache.org/)

### Supported Formats

 * HTML (built-in)
 * PDF (with XPDF or Solr)
 * Microsoft Word, Excel, Powerpoint (Solr)
 * OpenOffice (Solr)
 * CSV (Solr)
 * RTF (Solr)
 * EPub (Solr)
 * Many others (Tika)

## Installation

The recommended installation is through [composer](http://getcomposer.org).
Add the following to your `composer.json`:

	```js
	{
		"require": {
			"silverstripe/textextraction": "2.0.x-dev"
		}
	}
	```

The module depends on the [Guzzle HTTP Library](http://guzzlephp.org),
which is automatically checked out by composer. Alternatively, install Guzzle
through PEAR and ensure its in your `include_path`.

## Configuration

### Basic

By default, only extraction from HTML documents is supported.
No configuration is required for that, unless you want to make
the content available through your `DataObject` subclass.
In this case, add the following to `mysite/_config/config.yml`:

	```yaml
	File:
	  extensions:
	    - FileTextExtractable
	```

### XPDF

PDFs require special handling, for example through the [XPDF](http://www.foolabs.com/xpdf/)
commandline utility. Follow their installation instructions, its presence will be automatically
detected. You can optionally set the binary path in `mysite/_config/config.yml`:

	```yml
	PDFTextExtractor:
		binary_location: /my/path/pdftotext
	```

### Apache Solr

Apache Solr is a fulltext search engine, an aspect which is often used
alongside this module. But more importantly for us, it has bindings to [Apache Tika](http://tika.apache.org/)
through the [ExtractingRequestHandler](http://wiki.apache.org/solr/ExtractingRequestHandler) interface.
This allows Solr to inspect the contents of various file formats, such as Office documents and PDF files.
The textextraction module retrieves the output of this service, rather than altering the index.
With the raw text output, you can decide to store it in a database column for fulltext search
in your database driver, or even pass it back to Solr as part of a full index update.

In order to use Solr, you need to configure a URL for it (in `mysite/_config/config.yml`):

	```yml
	SolrCellTextExtractor:
		base_url: 'http://localhost:8983/solr/update/extract'
	```

Note that in case you're using multiple cores, you'll need to add the core name to the URL 
(e.g. 'http://localhost:8983/solr/PageSolrIndex/update/extract').
The ["fulltext" module](https://github.com/silverstripe-labs/silverstripe-fulltextsearch)
uses multiple cores by default, and comes prepackaged with a Solr server.
Its a stripped-down version of Solr, follow the module README on how to add
Apache Tika text extraction capabilities.

You need to ensure that some indexable property on your object
returns the contents, either by directly accessing `FileTextExtractable->extractFileAsText()`,
or by writing your own method around `FileTextExtractor->getContent()` (see "Usage" below).
The property should be listed in your `SolrIndex` subclass, e.g. as follows:

	```php
	class MyDocument extends DataObject {
		static $db = array('Path' => 'Text');
		function getContent() {
			$extractor = FileTextExtractor::for_file($this->Path);
			return $extractor ? $extractor->getContent($this->Path) : null;		
		}
	}
	class MySolrIndex extends SolrIndex {
		function init() {
			$this->addClass('MyDocument');
			$this->addStoredField('Content', 'HTMLText');
		}
	}
	```

Note: This isn't a terribly efficient way to process large amounts of files, since 
each HTTP request is run synchronously.

### Tika

Support for Apache Tika (1.7 and above) is included for the standalone command line utility.

See [the Apache Tika home page](http://tika.apache.org/1.7/index.html) for instructions on installing and
configuring this.

This extension will best work with the [fileinfo PHP extension](http://php.net/manual/en/book.fileinfo.php)
installed to perform mime detection. Tika validates support via mime type rather than file extensions.

## Usage

Manual extraction:

	$myFile = '/my/path/myfile.pdf';
	$extractor = FileTextExtractor::for_file($myFile);
	$content = $extractor->getContent($myFile);

Extraction with `FileTextExtractable` extension applied:

	$myFileObj = File::get()->First();
	$content = $myFileObj->extractFileAsText();
