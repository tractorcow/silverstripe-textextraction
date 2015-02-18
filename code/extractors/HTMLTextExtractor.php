<?php

/**
 * Text extractor that uses php function strip_tags to get just the text. OK for indexing, not the best for readable text.
 * @author mstephens
 *
 */
class HTMLTextExtractor extends FileTextExtractor {
	
	public function isAvailable() {
		return true;
	}

	public function supportsExtension($extension) {
		return in_array(
			strtolower($extension),
			array("html", "htm", "xhtml")
		);
	}

	public function supportsMime($mime) {
		return strtolower($mime) === 'text/html';
	}

	/**
	 * Lower priority because its not the most clever HTML extraction. If there is something better, use it
	 *
	 * @config
	 * @var integer
	 */
	private static $priority = 10;

	/**
	 * Extracts content from regex, by using strip_tags()
	 * combined with regular expressions to remove non-content tags like <style> or <script>,
	 * as well as adding line breaks after block tags.
	 * 
	 * @param string $path
	 * @return string
	 */
	public function getContent($path) {
		$content = file_get_contents($path);
		// Yes, yes, regex'ing HTML is evil.
		// Since we don't care about well-formedness or markup here, it does the job.
		$content = preg_replace( 
			array( 
				// Remove invisible content 
					'@<head[^>]*?>.*?</head>@siu', 
					'@<style[^>]*?>.*?</style>@siu', 
					'@<script[^>]*?.*?</script>@siu', 
					'@<object[^>]*?.*?</object>@siu', 
					'@<embed[^>]*?.*?</embed>@siu', 
					'@<applet[^>]*?.*?</applet>@siu', 
					'@<noframes[^>]*?.*?</noframes>@siu', 
					'@<noscript[^>]*?.*?</noscript>@siu', 
					'@<noembed[^>]*?.*?</noembed>@siu', 
				// Add line breaks before and after blocks 
					'@</?((address)|(blockquote)|(center)|(del))@iu', 
					'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu', 
					'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu', 
					'@</?((table)|(th)|(td)|(caption))@iu', 
					'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu', 
					'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu', 
					'@</?((frameset)|(frame)|(iframe))@iu', 
			), 
			array( 
				' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',"$0", "$0", "$0", "$0", "$0", "$0","$0", "$0",
			), 
			$content 
		); 
		return strip_tags($content);
	}
}
