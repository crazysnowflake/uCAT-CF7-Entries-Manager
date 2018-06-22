<?php
function u_cf7_entries_replace_tags( $content, $args = '', $posted_data ) {
	$args = wp_parse_args( $args, array(
		'html' => false,
		'exclude_blank' => true ) );

	if ( is_array( $content ) ) {
		foreach ( $content as $key => $value ) {
			$content[$key] = u_cf7_entries_replace_tags( $value, $args );
		}

		return $content;
	}

	$content = explode( "\n", $content );

	foreach ( $content as $num => $line ) {
		$line = new U_CF7_Entries_MailTaggedText( $line, $args, $posted_data );
		$replaced = $line->replace_tags();
			//var_dump($replaced);

		if ( $args['exclude_blank'] ) {
			$replaced_tags = $line->get_replaced_tags();

			if ( empty( $replaced_tags ) || array_filter( $replaced_tags ) ) {
				$content[$num] = $replaced;
			} else {
				unset( $content[$num] ); // Remove a line.
			}
		} else {
			$content[$num] = $replaced;
		}
	}

	$content = implode( "\n", $content );

	return $content;
}

class U_CF7_Entries_MailTaggedText  {

	private $html = false;
	private $content = '';
	private $replaced_tags = array();
	private $posted_data   = array();

	public function __construct( $content, $args = '', $posted_data = array() ) {
		$args = wp_parse_args( $args, array( 'html' => false ) );

		$this->html = (bool) $args['html'];
		$this->content = $content;
		$this->posted_data = $posted_data;
	}
	public function get_replaced_tags() {
		return $this->replaced_tags;
	}

	public function replace_tags() {
		$regex = '/(\[?)\[[\t ]*'
			. '([a-zA-Z_][0-9a-zA-Z:._-]*)' // [2] = name
			. '((?:[\t ]+"[^"]*"|[\t ]+\'[^\']*\')*)' // [3] = values
			. '[\t ]*\](\]?)/';

		if ( $this->html ) {
			$callback = array( $this, 'replace_tags_callback_html' );
		} else {
			$callback = array( $this, 'replace_tags_callback' );
		}

		return preg_replace_callback( $regex, $callback, $this->content);
	}

	private function replace_tags_callback_html( $matches ) {
		return $this->replace_tags_callback( $matches, true);
	}

	private function replace_tags_callback( $matches, $html = false ) {
		
		// allow [[foo]] syntax for escaping a tag
		if ( $matches[1] == '[' && $matches[4] == ']' ) {
			return substr( $matches[0], 1, -1 );
		}

		$tag = $matches[0];
		$tagname = $matches[2];
		$values = $matches[3];

		if ( ! empty( $values ) ) {
			preg_match_all( '/"[^"]*"|\'[^\']*\'/', $values, $matches );
			$values = wpcf7_strip_quote_deep( $matches[0] );
		}

		$do_not_heat = false;

		if ( preg_match( '/^_raw_(.+)$/', $tagname, $matches ) ) {
			$tagname = trim( $matches[1] );
			$do_not_heat = true;
		}

		$format = '';

		if ( preg_match( '/^_format_(.+)$/', $tagname, $matches ) ) {
			$tagname = trim( $matches[1] );
			$format = $values[0];
		}

		$submitted = isset($this->posted_data[$tagname]) ? $this->posted_data[$tagname] : null;

		if ( null !== $submitted ) {

			if ( $do_not_heat ) {
				$submitted = isset( $_POST[$tagname] ) ? $_POST[$tagname] : '';
			}

			$replaced = $submitted;

			if ( ! empty( $format ) ) {
				$replaced = $this->format( $replaced, $format );
			}

			$replaced = wpcf7_flat_join( $replaced );

			if ( $html ) {
				$replaced = esc_html( $replaced );
				$replaced = wptexturize( $replaced );
			}

			$replaced = apply_filters( 'wpcf7_mail_tag_replaced',
				$replaced, $submitted, $html );

			$replaced = wp_unslash( trim( $replaced ) );

			$this->replaced_tags[$tag] = $replaced;
			return $replaced;
		}

		$special = apply_filters( 'wpcf7_special_mail_tags', '', $tagname, $html );

		if ( ! empty( $special ) ) {
			$this->replaced_tags[$tag] = $special;
			return $special;
		}

		return $tag;
	}
	public function format( $original, $format ) {
		$original = (array) $original;

		foreach ( $original as $key => $value ) {
			if ( preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value ) ) {
				$original[$key] = mysql2date( $format, $value );
			}
		}

		return $original;
	}
}