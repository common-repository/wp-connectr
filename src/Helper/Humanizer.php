<?php

namespace WPConnectr\Helper;

// Source: https://github.com/coduo/php-humanizer/blob/4.x/src/Coduo/PHPHumanizer/String/Humanize.php

final class Humanizer {
	private $text;
	private $capitalize;
	private $separator;

	/**
	 * A list of words that should be removed during humanization
	 *
	 * @var array<string>
	 */
	private $forbiddenWords;

	/**
	 * Humanizer constructor
	 *
	 * @param string $text The raw unhumanized text
	 * @param boolean $capitalize True to capitalize the first letter
	 * @param string $separator The separator that is used in the text to humanize
	 * @param array $forbiddenWords A list of words that should be removed
	 */
	public function __construct( $text, $capitalize = true, $separator = '_', $forbiddenWords = [] ) {
		$this->text           = $text;
		$this->capitalize     = $capitalize;
		$this->separator      = $separator;
		$this->forbiddenWords = $forbiddenWords;
	}

	public function __toString() {
		$humanized = \trim(\strtolower( (string) \preg_replace( ['/([A-Z])/', \sprintf( '/[%s\s]+/', $this->separator ) ], [ '_$1', ' ' ], $this->text ) ) );
		$humanized = \trim(\str_replace( $this->forbiddenWords, '', $humanized ) );

		return $this->capitalize ?  \ucfirst( $humanized ) : $humanized;
	}
}
