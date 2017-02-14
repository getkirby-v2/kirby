<?php
	/****************************************************
	 * The following MIME sniffing class is based
	 * on the specification published at the following
	 * URL: http://mimesniff.spec.whatwg.org/
	 *
	 * To use this class, just pass the filename or
	 * a vile resource to the construct - the class
	 * will then examine the file and find the MIME
	 * type.
	 *
	 * The Public API:
	 *  - get_type:	Gets the determined MIME type,
	 *				returns application/octet-stream
	 *				if the MIME type is unknown.
	 *  - is_empty: Whether or not the file is empty
	 *  - is_text:	Whether or not the file is plain
	 *				text (UTF-8 or not)
	 *  - is_font:  Whether the file is a font file
	 *  - is_zip:   Whether the file is zipped
	 *  - is_archive: Whether the file is an archive
	 *  - is_scriptable:
	 *				Determines whether the selected
	 *				MIME type may contain executable
	 *				scripts
	 ***************************************************/
	class MimeReader {

		protected $file = null, $detected_type = null, $header = null;
		protected static $binary_characters = '', $whitespace_characters = '', $tag_terminating_characters = '';
		protected static $image = null, $media = null, $fonts = null, $archive = null, $text = null, $others = null, $unknown = null, $html = null;


		public function __construct( $file ) {
			$this->file			= $file;

			if ( empty( self::$binary_characters ) ) {
				self::$binary_characters .= "\x00\x01\x02\x03\x04\x05\x06\x07\0x08\x0B\x0E\x0F\x10\x11";
				self::$binary_characters .= "\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1C\x1D\x1E\x1F";
			}
			if ( empty( self::$whitespace_characters ) ) {
				self::$whitespace_characters .= "\x09\x0A\x0C\x0D\x20";
			}
			if ( empty( self::$tag_terminating_characters ) ) {
				self::$tag_terminating_characters .= "\x20\x3E";
			}

			if ( is_null( self::$image ) ) {
				$image	= &self::$image;
				$image	= array();

				// Windows Icon
				$image[] = array (
					'mime'		=> 'image/vnd.microsoft.icon',
					'pattern'	=> "\x00\x00\x01\x00",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> '',	// none
				);
				// "BM" - BMP signature
				$image[] = array (
					'mime'		=> 'image/bmp',
					'pattern'	=> "\x42\x4D",
					'mask'		=> "\xFF\xFF",
					'ignore'	=> ''
				);
				// "GIF87a" - GIF signature
				$image[] = array (
					'mime'		=> 'image/gif',
					'pattern'	=> "\x47\x49\x46\x38\x37\x61",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "GIF89a" - GIF signature
				$image[] = array (
					'mime'		=> 'image/gif',
					'pattern'	=> "\x47\x49\x46\x38\x39\x61",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "RIFF" followed by 4 bytes followed by "WEBPVP"
				$image[] = array (
					'mime'		=> 'image/webp',
					'pattern'	=> "\x52\x49\x46\x46\x00\x00\x00\x00\x57\x45\x42\x50\x56\x50",
					'mask'		=> "\xFF\xFF\xFF\xFF\x00\x00\x00\x00\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// A byte with only the highest bit set followed by the string "PNG" followed by CR LF SUB LF - PNG signature
				$image[] = array (
					'mime'		=> 'image/png',
					'pattern'	=> "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// JPEG start of image marker followed by another marker
				$image[] = array (
					'mime'		=> 'image/jpeg',
					'pattern'	=> "\xFF\xD8\xFF",
					'mask'		=> "\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// PSD signature
				$image[] = array (
					'mime'		=> 'application/psd',
					'pattern'	=> "\x38\x42\x50\x53",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
			}
			if ( is_null( self::$media ) ) {
				$media	= &self::$media;
				$media	= array();

				// The WebM signature
				$media[] = array (
					'mime'		=> 'video/webm',
					'pattern'	=> "\x1A\x45\xDF\xA3",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// The .snd signature
				$media[] = array (
					'mime'		=> 'audio/basic',
					'pattern'	=> "\x2E\x73\x6E\x64",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "FORM" followed by 4 bytes followed by "AIFF" - the AIFF signature
				$media[] = array (
					'mime'		=> 'audio/aiff',
					'pattern'	=> "\x46\x4F\x52\x4D\x00\x00\x00\x00\x41\x49\x46\x46",
					'mask'		=> "\xFF\xFF\xFF\xFF\x00\x00\x00\x00\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// MP3 without ID3 tag		/****** UNTESTED ******/
				$media[] = array (
					'mime'		=> 'audio/mpeg',
					'pattern'	=> "\xFF\xFB",
					'mask'		=> "\xFF\xFF",
					'ignore'	=> ''
				);
				// "ID3" and the ID3v2-tagged MP3 signature
				$media[] = array (
					'mime'		=> 'audio/mpeg',
					'pattern'	=> "\x49\x44\x33",
					'mask'		=> "\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "OggS" followed by NUL - The OGG signature
				$media[] = array (
					'mime'		=> 'application/ogg',
					'pattern'	=> "\x4F\x67\x67\x53\x00",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "MThd" followed by 4 bytes representing the number 6 in 32 bits (big endian) - MIDI signature
				$media[] = array (
					'mime'		=> 'audio/midi',
					'pattern'	=> "\x4D\x54\x68\x64\x00\x00\x00\x06",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "RIFF" followed by 4 bytes followed by "AVI" - AVI signature
				$media[] = array (
					'mime'		=> 'video/avi',
					'pattern'	=> "\x52\x49\x46\x46\x00\x00\x00\x00\x41\x56\x49\x20",
					'mask'		=> "\xFF\xFF\xFF\xFF\x00\x00\x00\x00\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "RIFF" followed by 4 bytes followed by "WAVE" - WAVE signature
				$media[] = array (
					'mime'		=> 'audio/wave',
					'pattern'	=> "\x52\x49\x46\x46\x00\x00\x00\x00\x57\x41\x56\x45",
					'mask'		=> "\xFF\xFF\xFF\xFF\x00\x00\x00\x00\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
			}
			if ( is_null( self::$fonts ) ) {
				$fonts		= &self::$fonts;
				$fonts		= array();

				// 34 bytes followed by "LP" - Opentype signature
				$fonts[] = array (
					'mime'		=> 'application/vnd.ms-fontobject',
					'pattern'	=> "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00" .
									"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x4C\x50",
					'mask'		=> "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00" .
									"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xFF\xFF",
					'ignore'	=> ''
				);
				// 4 bytes representing version type 1 of true type font
				$fonts[] = array (
					'mime'		=> 'application/font-ttf',
					'pattern'	=> "\x00\x01\x00\x00",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "OTTO" - Opentype signature
				$fonts[] = array (
					'mime'		=> 'application/font-off',		// application/vnd.ms-opentype
					'pattern'	=> "\x4F\x54\x54\x4F",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "ttcf" - Truetype Collection signature
				$fonts[] = array (
					'mime'		=> 'application/x-font-truetype-collection',
					'pattern'	=> "\x74\x74\x63\x66",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// 'wOFF' - Web Open Font Format signature
				$fonts[] = array (
					'mime'		=> 'application/font-woff',
					'pattern'	=> "\x77\x4F\x46\x46",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
			}
			if ( is_null( self::$archive ) ) {
				$archive	= &self::$archive;
				$archive	= array();

				// GZIP signature
				$archive[] = array (
					'mime'		=> 'application/x-gzip',
					'pattern'	=> "\x1F\x8B\x08",
					'mask'		=> "\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "PK" followed by ETX, EOT - ZIP signature
				$archive[] = array (
					'mime'		=> 'application/zip',
					'pattern'	=> "\x50\x4B\x03\x04",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "Rar " followed by SUB, BEL, NUL - RAR signature
				$archive[] = array (
					'mime'		=> 'application/x-rar-compressed',
					'pattern'	=> "\x52\x61\x72\x20\x1A\x07\x00",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
			}
			if ( is_null( self::$text ) ) {
				$text	= &self::$text;
				$text	= array();

				// "%!PS-Adobe-" - Postscript signature
				$text[] = array (
					'mime'		=> 'application/postscript',
					'pattern'	=> "\x25\x50\x53\x2D\x41\x64\x6F\x62\x65",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// UTF-16 Big Endian BOM text
				$text[] = array (
					'mime'		=> 'text/plain',
					'pattern'	=> "\xFF\xFE",
					'mask'		=> "\xFF\xFF",
					'ignore'	=> ''
				);
				// UTF-16 Little Endian BOM text
				$text[] = array (
					'mime'		=> 'text/plain',
					'pattern'	=> "\xFE\xFF",
					'mask'		=> "\xFF\xFF",
					'ignore'	=> ''
				);
				// UTF-8 BOM text
				$text[] = array (
					'mime'		=> 'text/plain',
					'pattern'	=> "\xEF\xBB\xBF",
					'mask'		=> "\xFF\xFF\xFF",
					'ignore'	=> ''
				);
			}
			if ( is_null( self::$others ) ) {
				$others		= &self::$others;
				$others		= array();

				$others[] = array (
					'mime'		=> 'WINDOWS EXECUTABLE',
					'pattern'	=> "\x4D\x5A",
					'mask'		=> "\xFF\xFF",
					'ignore'	=> ''
				);
				$others[] = array (
					'mime'		=> 'EXEC_LINKABLE',
					'pattern'	=> "\x7F\x45\x4C\x46",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);

			}
			if ( is_null( self::$unknown ) ) {
				$unknown	= &self::$unknown;
				$unknown	= array();

				// "<!DOCTYPE HTML"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x21\x44\x4F\x43\x54\x59\x50\x45\x20\x48\x54\x4D\x4C",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<HTML"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x48\x54\x4D\x4C",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<HEAD"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x48\x45\x41\x44",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<SCRIPT"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x53\x43\x52\x49\x50\x54",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<IFRAME"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x49\x46\x52\x41\x4D\x45",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<H1"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x48\x31",
					'mask'		=> "\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<DIV"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x44\x49\x56",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<FONT"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x46\x4F\x4E\x54",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<TABLE"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x54\x41\x42\x4C\x45",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<A"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x41",
					'mask'		=> "\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<STYLE"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x53\x54\x59\x4C\x45",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<TITLE"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x54\x49\x54\x4C\x45",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<B"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x42",
					'mask'		=> "\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<BODY"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x42\x4F\x44\x59",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<BR"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x42\x52",
					'mask'		=> "\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<P"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x50",
					'mask'		=> "\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "<!--"
				$unknown[] = array (
					'mime'		=> 'text/html',
					'pattern'	=> "\x3C\x21\x2D\x2D",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				$unknown[] = array (
					'mime'		=> 'text/xml',
					'pattern'	=> "\x3C\x3F\x78\x6D\x6C",
					'mask'		=> "\xFF\xFF\xFF\xFF\xFF",
					'ignore'	=> self::$whitespace_characters
				);
				// "%PDF" - PDF signature
				$unknown[] = array (
					'mime'		=> 'application/pdf',
					'pattern'	=> "\x25\x50\x44\x46",
					'mask'		=> "\xFF\xFF\xFF\xFF",
					'ignore'	=> ''
				);
				// "PK" followed by 18 bytes - Microsoft Open Office signature
				$unknown[] = array (
					'mime'		=> 'application/docx',
					'pattern'	=> "\x50\x4B\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
					'mask'		=> "\xFF\xFF\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
					'ignore'	=> ''
				);
				// 
			}

			$this->read_resource_header();
			$this->detect_type();
		}

		/* The public API */
		public function is_empty() {
			if ( 'inode/x-empty' === $this->detected_type ) return true;

			return false;
		}

		public function is_text() {
			if ( 'application/postscript'	=== $this->detected_type ) return true;
			if ( 'text/plain'				=== $this->detected_type ) return true;

			return false;
		}

		public function is_font() {
			if ( $this->detected_type	=== 'application/font-ttf' ) return true;
			if ( $this->detected_type	=== 'application/font-cff' ) return true;
			if ( $this->detected_type	=== 'application/font-otf' ) return true;
			if ( $this->detected_type	=== 'application/font-sntf' ) return true;
			if ( $this->detected_type	=== 'application/vds.ms-opentype' ) return true;
			if ( $this->detected_type	=== 'application/font-woff' ) return true;
			if ( $this->detected_type	=== 'application/vnd.ms-fontobject' ) return true;

			return false;
		}

		public function is_zip() {
			if ( $this->detected_type	=== 'application/zip' ) return true;
			if ( substr( $this->detected_type, -4 ) === '+zip' ) return true;

			return false;
		}

		public function is_archive() {
			if ( $this->detected_type	=== 'application/x-rar-compressed' ) return true;
			if ( $this->detected_type	=== 'application/zip' ) return true;
			if ( $this->detected_type	=== 'application/x-gzip' ) return true;

			return false;
		}

		public function is_scriptable() {
			if ( $this->detected_type	=== 'text/html' ) return true;
			if ( $this->detected_type	=== 'application/pdf' ) return true;
			if ( $this->detected_type	=== 'application/postscript' ) return true;

			return false;
		}

		public function get_type() {
			return $this->detected_type;
		}

		/**
		 *  Helper functions.
		 *   Execution is passed over to detect_type after the
		 *   construct sets up the data.
		 *   
		 */
		protected function read_resource_header() {
			if ( is_string( $this->file ) ) {
				$fp		= fopen( $this->file, 'r' );
				$header	= fread( $fp, 512 );
				fclose( $fp );
			} else {
				// The current position may not be at the start. Let's take it then set to
				//  start of file, read 512 bytes then reset to last position.
				$position	= ftell( $this->file );
				fseek( $this->file, 0, SEEK_SET );
				$header		= fread( $this->file, 512 );
				fseek( $this->file, $position, SEEK_SET );
			}

			$this->header	= &$header;
		}

		protected function match_pattern( $pattern, $mask, $ignore ) {
			if ( empty( $pattern ) || empty( $mask ) ) {
				return false;
			}
			$s	= 0;

			$sequence	= &$this->header;
			$seq_len		= strlen( $sequence );
			$pattern_len	= strlen( $pattern );
			$mask_len		= strlen( $mask );
			if ( $pattern_len !== $mask_len ) {
				return false;
			}

			// First we will set $s so that it ignores the first bytes if it needs to
			if ( !empty( $ignore ) ) {
				for ( $s = 0; $s < $seq_len; ) {
					// This letter should not be ignored.
					if ( strpos( $ignore, $sequence{$s} ) === false ) {
						break;
					}

					++$s;
				}
			}

			// Now we will compare. If it doesn't match the mask, we return false.
			for ( $i = 0; $i < $pattern_len; ) {
				$masked_data	= @$sequence{$s} & @$mask{$i};

				if ( $masked_data !== $pattern{$i} ) {
					return false;
				}

				++$i; ++$s;
			}

			// Mask matched. This pattern matches.
			return true;
		}

		protected function html_match_pattern( $pattern, $mask, $ignore ) {
			if ( empty( $pattern ) || empty( $mask ) ) {
				return false;
			}
			$s	= 0; $i = 0;

			$sequence	= &$this->header;
			$seq_len		= strlen( $sequence );
			$pattern_len	= strlen( $pattern );
			$mask_len		= strlen( $mask );

			if ( $pattern_len !== $mask_len ) {
				return false;
			}

			// First we will set $s so that it ignores the first bytes if it needs to
			if ( !empty( $ignore ) ) {
				for (; $s < $seq_len; ) {
					// This letter should not be ignored.
					if ( strpos( $ignore, $sequence{$s} ) === false ) {
						break;
					}

					++$s;
				}
			}

			// Now we will compare. If it doesn't match the mask, we return false.
			for (; $i < $pattern_len; ) {
				$masked_data	= $sequence{$s} & $mask{$i};

				if ( $masked_data !== $pattern{$i} ) {
					return false;
				}

				++$i; ++$s;
			}

			// Mask matched. This pattern matches if the last character is tag-terminating.
			return strpos( self::$tag_terminating_character, $sequence{$s} );
		}

		protected function detect_type() {
			if ( $this->sniff_empty() )   return;
			if ( $this->sniff_images() )  return;
			if ( $this->sniff_media() )   return;
			if ( $this->sniff_fonts() )   return;
			if ( $this->sniff_archive() ) return;
			if ( $this->sniff_text() )    return;
			if ( $this->sniff_unknown() ) return;
			if ( $this->sniff_others() )  return;
		}

		/* Sniffer functions */
		protected function sniff_empty() {
			if (strlen($this->header) === 0) {
				$this->detected_type    = 'inode/x-empty';
				return true;
			}

			return false;
		}

		protected function sniff_images() {
			$num_imgs	= count( self::$image );
			for ( $i = 0; $i < $num_imgs; $i++ ) {
				$im	= &self::$image[$i];

				if ( $this->match_pattern( $im['pattern'], $im['mask'], $im['ignore'] ) ) {
					$this->detected_type	= $im['mime'];
					return true;
				}
			}

			return false;
		}

		protected function sniff_media() {
			$num_media	= count( self::$media );
			for ( $i = 0; $i < $num_media; $i++ ) {
				$m = &self::$media[$i];
				if ( $this->match_pattern( $m['pattern'], $m['mask'], $m['ignore'] ) ) {
					$this->detected_type = $m['mime'];
					return true;
				}
			}

			if ( $this->sniff_mp4() ) {
				$this->detected_type = 'video/mp4';
				return true;
			}

			return false;
		}

		protected function sniff_mp4() {
			$sequence	= &$this->header;
			$seq_len	= strlen( $sequence );

			if ( $seq_len < 12 ) {
				return false;
			}

			$box_size	= substr( $sequence, 0, 4 );
			$box_size	= unpack( 'N', $box_size );
			$box_size	= $box_size[1];

			if ( $seq_len < $box_size ) return false;
			if ( $box_size % 4 ) return false;

			if ( substr( $sequence, 4, 4 ) !== "\x66\x74\x79\x70" ) return false;
			if ( substr( $sequence, 8, 3 ) === "\x6D\x70\x34" ) return true;

			$i = 16;
			while ( $i < $box_size ) {
				if ( substr( $sequence, $i, 3 ) === "\x6D\x70\x34" ) return true;
				$i += 4;
			}

			return false;
		}

		protected function sniff_fonts() {
			$num_fonts	= count( self::$fonts );
			for ( $i = 0; $i < $num_fonts; $i++ ) {
				$f	= &self::$fonts[$i];
				if ( $this->match_pattern( $f['pattern'], $f['mask'], $f['ignore'] ) ) {
					$this->detected_type = $f['mime'];
					return true;
				}
			}

			return false;
		}

		protected function sniff_archive() {
			$num_archives = count( self::$archive );
			for ( $i = 0; $i < $num_archives; $i++ ) {
				$a = &self::$archive[$i];
				if ( $this->match_pattern( $a['pattern'], $a['mask'], $a['ignore'] ) ) {
					$this->detected_type = $a['mime'];
					return true;
				}
			}

			return false;
		}

		protected function sniff_text() {
			$num_texts = count( self::$text );
			for ( $i = 0; $i < $num_texts; $i++ ) {
				$t	= &self::$text[$i];
				if ( $this->match_pattern( $t['pattern'], $t['mask'], $t['ignore'] ) ) {
					if ( $this->has_binary_data() ) {
						return false;
					} else {
						$this->detected_type	= $t['mime'];
						return true;
					}
				}
			}

			return false;
		}

		protected function sniff_unknown() {
			$num_unknown = count( self::$unknown );
			for ( $i = 0; $i < $num_unknown; $i++ ) {
				$u = &self::$unknown[$i];
				if ( 'text/html' === $u['mime'] ) {
					if ( $this->html_match_pattern( $u['pattern'], $u['mask'], $u['ignore'] ) ) {
						$this->detected_type = 'text/html';
						return true;
					}
				} else {
					if ( $this->match_pattern( $u['pattern'], $u['mask'], $u['ignore'] ) ) {
						$this->detected_type = $u['mime'];
						return true;
					}
				}
			}

			return false;
		}

		protected function sniff_others() {
			$num_others = count( self::$others );
			for ( $i = 0; $i < $num_others; $i++ ) {
				$o = &self::$others[$i];
				if ( $this->match_pattern( $o['pattern'], $o['mask'], $o['ignore'] ) ) {
					$this->detected_type = $o['mime'];
					return true;
				}
			}

			return false;
		}

		protected function has_binary_data() {
			static $binary_chars;

			if ( is_string( $binary_chars ) )
				$binary_chars = str_split( $this->binary_characters );

			$num_chars	= count( $binary_chars );
			for ( $i = 0; $i < $num_chars; $i++ ) {
				if ( strpos( $this->header, $binary_chars[$i] ) !== false ) {
					return true;
				}
			}

			return false;
		}
	}
