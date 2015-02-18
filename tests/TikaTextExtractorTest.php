<?php

/**
 * Tests the {@see TikaTextExtractor} class
 */
class TikaTextExtractorTest extends SapphireTest {
	
	function testExtraction() {
		$extractor = new TikaTextExtractor();
		if(!$extractor->isAvailable()) {
			exec('tika --version', $output);
			Debug::dump($output);
			exec('~/bin/tika --version', $output);
			Debug::dump($output);
			exec('/home/travis/bin/tika --version', $output);
			Debug::dump($output);
			$this->markTestSkipped('tika not available in path "'.getenv('PATH').'"');
		}

		// Check file
		$file = Director::baseFolder() . '/textextraction/tests/fixtures/test1.pdf';
		$content = $extractor->getContent($file);
		$this->assertContains('This is a test file with a link', $content);

		// Check mime validation
		$this->assertTrue($extractor->supportsMime('application/pdf'));
		$this->assertTrue($extractor->supportsMime('text/html'));
		$this->assertFalse($extractor->supportsMime('application/not-supported'));
	}

}