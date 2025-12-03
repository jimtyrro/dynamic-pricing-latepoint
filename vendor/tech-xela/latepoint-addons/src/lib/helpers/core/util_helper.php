<?php
/*
 * LatePoint Addons Framework
 * Copyright (c) 2021-2023 TechXela (https://codecanyon.net/user/tech-xela). All Rights Reserved.
 *
 * LICENSE
 * -------
 * This software is furnished under license(s) and may be used and copied only in accordance with the terms of such
 * license(s) along with the inclusion of the above copyright notice. If you purchased an item through CodeCanyon, in
 * which this software came included, please read the full license(s) at: https://codecanyon.net/licenses/standard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'TechXelaLatePointUtilHelper' ) ) {

	final class TechXelaLatePointUtilHelper {

		public static function ellipseString( $string, $length ): string {
			return substr( $string, 0, $length ) . ( strlen( $string ) > $length ? '...' : '' );
		}

		public static function extractIdsAndQuantities( $rawIdsAndQuantities = [] ): array {
			$rawIdsAndQuantities   = ( is_array( $rawIdsAndQuantities ) ) ? $rawIdsAndQuantities : explode( ',', $rawIdsAndQuantities );
			$cleanIdsAndQuantities = [];
			if ( is_array( $rawIdsAndQuantities ) ) {
				foreach ( $rawIdsAndQuantities as $service_extra_id ) {
					list( $id, $quantity ) = array_pad( explode( ':', $service_extra_id ), 2, 1 );
					if ( is_numeric( $id ) && is_numeric( $quantity ) ) {
						$cleanIdsAndQuantities[ $id ] = $quantity;
					}
				}
			}

			return $cleanIdsAndQuantities;
		}

		public static function emptyRecursive( $value ): bool {
			if ( is_array( $value ) ) {
				$empty = true;
				array_walk_recursive( $value, function ( $item ) use ( &$empty ) {
					$empty = $empty && empty( $item );
				} );
			} else {
				$empty = empty( $value );
			}

			return $empty;
		}

		public static function wrapElementInDoubleCurlyBraces( $element ): string {
			return "{{{$element}}}";
		}

		public static function wrapElementsInDoubleCurlyBraces( array $elements ): array {
			return array_map( function ( $element ) {
				return self::wrapElementInDoubleCurlyBraces( $element );
			}, $elements );
		}

		public static function getIPNatively( $getHostByAddr = false ) {
			foreach (
				array(
					'HTTP_CLIENT_IP',
					'HTTP_X_FORWARDED_FOR',
					'HTTP_X_FORWARDED',
					'HTTP_X_CLUSTER_CLIENT_IP',
					'HTTP_FORWARDED_FOR',
					'HTTP_FORWARDED',
					'REMOTE_ADDR'
				) as $key
			) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) {
						if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
							if ( $getHostByAddr === true ) {
								return getHostByAddr( $ip );
							} else {
								return $ip;
							}
						}
					}
				}
			}

			return false;
		}

		public static function getIpRemotely() {
			$wp_args            = array( 'method' => 'GET' );
			$wp_args['timeout'] = 30;
			$result             = wp_remote_request( 'https://ipecho.net/plain', $wp_args );
			if ( is_wp_error( $result ) ) {
				return false;
			}

			return $result['body'];
		}

		/**
		 * Parse a query string into an associative array.
		 *
		 * If multiple values are found for the same key, the value of that key
		 * value pair will become an array. This function does not parse nested
		 * PHP style arrays into an associative array (e.g., `foo[a]=1&foo[b]=2`
		 * will be parsed into `['foo[a]' => '1', 'foo[b]' => '2'])`.
		 *
		 * @param string $str Query string to parse
		 * @param int|bool $urlEncoding How the query string is encoded
		 *
		 * @return array
		 */
		public static function parseQuery( $str, $urlEncoding = true ) {
			$result = [];

			if ( $str === '' ) {
				return $result;
			}

			if ( $urlEncoding === true ) {
				$decoder = function ( $value ) {
					return rawurldecode( str_replace( '+', ' ', $value ) );
				};
			} elseif ( $urlEncoding === PHP_QUERY_RFC3986 ) {
				$decoder = 'rawurldecode';
			} elseif ( $urlEncoding === PHP_QUERY_RFC1738 ) {
				$decoder = 'urldecode';
			} else {
				$decoder = function ( $str ) {
					return $str;
				};
			}

			foreach ( explode( '&', $str ) as $kvp ) {
				$parts = explode( '=', $kvp, 2 );
				$key   = $decoder( $parts[0] );
				$value = isset( $parts[1] ) ? $decoder( $parts[1] ) : null;
				if ( ! isset( $result[ $key ] ) ) {
					$result[ $key ] = $value;
				} else {
					if ( ! is_array( $result[ $key ] ) ) {
						$result[ $key ] = [ $result[ $key ] ];
					}
					$result[ $key ][] = $value;
				}
			}

			return $result;
		}

		/**
		 * Returns all traits used by a class, its parent classes and trait of their traits.
		 *
		 * @param object|string $class
		 *
		 * @return array
		 */
		public static function classUsesRecursive( $class ): array {
			if ( is_object( $class ) ) {
				$class = get_class( $class );
			}

			$results = [];

			foreach ( array_reverse( class_parents( $class ) ) + [ $class => $class ] as $class ) {
				$results += self::traitUsesRecursive( $class );
			}

			return array_unique( $results );
		}

		/**
		 * Returns all traits used by a trait and its traits.
		 *
		 * @param string $trait
		 *
		 * @return array
		 */
		public static function traitUsesRecursive( $trait ): array {
			$traits = class_uses( $trait ) ?: [];

			foreach ( $traits as $trait ) {
				$traits += self::traitUsesRecursive( $trait );
			}

			return $traits;
		}

		public static function classUsesTrait( $objectOrClass, string $class ): bool {
			return in_array( $class, self::classUsesRecursive( $objectOrClass ) );
		}

		public static function getCountriesList(): array {
			return [
				"AF" => "Afghanistan",
				"AX" => "Åland Islands",
				"AL" => "Albania",
				"AS" => "American Samoa",
				"AD" => "Andorra",
				"AO" => "Angola",
				"AI" => "Anguilla",
				"AG" => "Antigua And Barbuda",
				"AR" => "Argentina",
				"AM" => "Armenia",
				"AW" => "Aruba",
				"AU" => "Australia",
				"AT" => "Austria",
				"AZ" => "Azerbaijan",
				"BS" => "Bahamas",
				"BH" => "Bahrain",
				"BB" => "Barbados",
				"BY" => "Belarus",
				"BE" => "Belgium",
				"BZ" => "Belize",
				"BJ" => "Benin",
				"BM" => "Bermuda",
				"BT" => "Bhutan",
				"BQ" => "Bonaire, Sint Eustatius and Saba",
				"BA" => "Bosnia and Herzegovina",
				"BW" => "Botswana",
				"BV" => "Bouvet Island",
				"BR" => "Brazil",
				"IO" => "British Indian Ocean Territory",
				"BN" => "Brunei Darussalam",
				"BG" => "Bulgaria",
				"BF" => "Burkina Faso",
				"BI" => "Burundi",
				"CM" => "Cameroon",
				"CA" => "Canada",
				"CV" => "Cape Verde",
				"KY" => "Cayman Islands",
				"CF" => "Central African Republic",
				"TD" => "Chad",
				"CL" => "Chile",
				"CN" => "China",
				"CX" => "Christmas Island",
				"CC" => "Cocos (Keeling) Islands",
				"CO" => "Colombia",
				"KM" => "Comoros",
				"CG" => "Congo",
				"CD" => "Congo, the Democratic Republic of the",
				"CK" => "Cook Islands",
				"CR" => "Costa Rica",
				"CI" => "Côte d'Ivoire",
				"HR" => "Croatia",
				"CW" => "Curaçao",
				"CY" => "Cyprus",
				"CZ" => "Czech Republic",
				"DK" => "Denmark",
				"DJ" => "Djibouti",
				"DM" => "Dominica",
				"DO" => "Dominican Republic",
				"SV" => "El Salvador",
				"GQ" => "Equatorial Guinea",
				"ER" => "Eritrea",
				"EE" => "Estonia",
				"ET" => "Ethiopia",
				"FK" => "Falkland Islands (Malvinas)",
				"FO" => "Faroe Islands",
				"FJ" => "Fiji",
				"FI" => "Finland",
				"FR" => "France",
				"GF" => "French Guiana",
				"PF" => "French Polynesia",
				"TF" => "French Southern Territories",
				"GA" => "Gabon",
				"GM" => "Gambia",
				"GE" => "Georgia",
				"GH" => "Ghana",
				"GI" => "Gibraltar",
				"GR" => "Greece",
				"GL" => "Greenland",
				"GD" => "Grenada",
				"GP" => "Guadeloupe",
				"GU" => "Guam",
				"GT" => "Guatemala",
				"GG" => "Guernsey",
				"GN" => "Guinea",
				"GW" => "Guinea-Bissau",
				"GY" => "Guyana",
				"HT" => "Haiti",
				"HM" => "Heard Island and McDonald Islands",
				"VA" => "Holy See (Vatican City State)",
				"HN" => "Honduras",
				"HK" => "Hong Kong",
				"HU" => "Hungary",
				"IS" => "Iceland",
				"IN" => "India",
				"IE" => "Ireland",
				"IM" => "Isle of Man",
				"IL" => "Israel",
				"IT" => "Italy",
				"JM" => "Jamaica",
				"JP" => "Japan",
				"JE" => "Jersey",
				"JO" => "Jordan",
				"KZ" => "Kazakhstan",
				"KE" => "Kenya",
				"KI" => "Kiribati",
				"KR" => "Korea, Republic of",
				"XK" => "Kosovo",
				"KW" => "Kuwait",
				"LA" => "Lao People's Democratic Republic",
				"LV" => "Latvia",
				"LB" => "Lebanon",
				"LS" => "Lesotho",
				"LR" => "Liberia",
				"LY" => "Libyan Arab Jamahiriya",
				"LI" => "Liechtenstein",
				"LT" => "Lithuania",
				"LU" => "Luxembourg",
				"MO" => "Macao",
				"MG" => "Madagascar",
				"MW" => "Malawi",
				"MY" => "Malaysia",
				"MV" => "Maldives",
				"ML" => "Mali",
				"MT" => "Malta",
				"MH" => "Marshall Islands",
				"MQ" => "Martinique",
				"MR" => "Mauritania",
				"MU" => "Mauritius",
				"YT" => "Mayotte",
				"MX" => "Mexico",
				"FM" => "Micronesia, Federated States of",
				"MD" => "Moldova, Republic of",
				"MC" => "Monaco",
				"MN" => "Mongolia",
				"ME" => "Montenegro",
				"MS" => "Montserrat",
				"MZ" => "Mozambique",
				"MM" => "Myanmar",
				"NA" => "Namibia",
				"NR" => "Nauru",
				"NL" => "Netherlands",
				"NC" => "New Caledonia",
				"NZ" => "New Zealand",
				"NI" => "Nicaragua",
				"NE" => "Niger",
				"NG" => "Nigeria",
				"NU" => "Niue",
				"NF" => "Norfolk Island",
				"MP" => "Northern Mariana Islands",
				"NO" => "Norway",
				"OM" => "Oman",
				"PW" => "Palau",
				"PA" => "Panama",
				"PG" => "Papua New Guinea",
				"PY" => "Paraguay",
				"PE" => "Peru",
				"PH" => "Philippines",
				"PN" => "Pitcairn",
				"PL" => "Poland",
				"PT" => "Portugal",
				"PR" => "Puerto Rico",
				"QA" => "Qatar",
				"RE" => "Réunion",
				"RO" => "Romania",
				"RU" => "Russian Federation",
				"RW" => "Rwanda",
				"BL" => "Saint Barthélemy",
				"SH" => "Saint Helena",
				"KN" => "Saint Kitts and Nevis",
				"LC" => "Saint Lucia",
				"MF" => "Saint Martin (French Part)",
				"PM" => "Saint Pierre and Miquelon",
				"VC" => "Saint Vincent and the Grenadines",
				"WS" => "Samoa",
				"SM" => "San Marino",
				"ST" => "Sao Tome and Principe",
				"SA" => "Saudi Arabia",
				"SN" => "Senegal",
				"RS" => "Serbia",
				"SC" => "Seychelles",
				"SL" => "Sierra Leone",
				"SG" => "Singapore",
				"SX" => "Sint Maarten (Dutch Part)",
				"SK" => "Slovakia",
				"SI" => "Slovenia",
				"SB" => "Solomon Islands",
				"SO" => "Somalia",
				"ZA" => "South Africa",
				"GS" => "South Georgia and the South Sandwich Islands",
				"SS" => "South Sudan",
				"ES" => "Spain",
				"LK" => "Sri Lanka",
				"SD" => "Sudan",
				"SR" => "Suriname",
				"SJ" => "Svalbard and Jan Mayen",
				"SZ" => "Swaziland",
				"SE" => "Sweden",
				"CH" => "Switzerland",
				"TW" => "Taiwan, Province of China",
				"TJ" => "Tajikistan",
				"TZ" => "Tanzania, United Republic of",
				"TH" => "Thailand",
				"TL" => "Timor-Leste",
				"TG" => "Togo",
				"TK" => "Tokelau",
				"TO" => "Tonga",
				"TT" => "Trinidad and Tobago",
				"TN" => "Tunisia",
				"TM" => "Turkmenistan",
				"TC" => "Turks and Caicos Islands",
				"TV" => "Tuvalu",
				"UG" => "Uganda",
				"UA" => "Ukraine",
				"AE" => "United Arab Emirates",
				"GB" => "United Kingdom",
				"US" => "United States",
				"UM" => "United States Minor Outlying Islands",
				"UY" => "Uruguay",
				"UZ" => "Uzbekistan",
				"VU" => "Vanuatu",
				"VE" => "Venezuela",
				"VG" => "Virgin Islands, British",
				"VI" => "Virgin Islands, U.S.",
				"WF" => "Wallis and Futuna",
				"EH" => "Western Sahara",
				"YE" => "Yemen",
				"ZM" => "Zambia",
				"ZW" => "Zimbabwe"
			];
		}

		public static function getCurrenciesList(): array {
			return [
				'ALL' => 'Albania Lek',
				'AFN' => 'Afghanistan Afghani',
				'ARS' => 'Argentina Peso',
				'AWG' => 'Aruba Guilder',
				'AUD' => 'Australia Dollar',
				'AZN' => 'Azerbaijan New Manat',
				'BSD' => 'Bahamas Dollar',
				'BBD' => 'Barbados Dollar',
				'BDT' => 'Bangladeshi taka',
				'BYR' => 'Belarus Ruble',
				'BZD' => 'Belize Dollar',
				'BMD' => 'Bermuda Dollar',
				'BOB' => 'Bolivia Boliviano',
				'BAM' => 'Bosnia and Herzegovina Convertible Marka',
				'BWP' => 'Botswana Pula',
				'BGN' => 'Bulgaria Lev',
				'BRL' => 'Brazil Real',
				'BND' => 'Brunei Darussalam Dollar',
				'KHR' => 'Cambodia Riel',
				'CAD' => 'Canada Dollar',
				'KYD' => 'Cayman Islands Dollar',
				'CLP' => 'Chile Peso',
				'CNY' => 'China Yuan Renminbi',
				'COP' => 'Colombia Peso',
				'CRC' => 'Costa Rica Colon',
				'HRK' => 'Croatia Kuna',
				'CUP' => 'Cuba Peso',
				'CZK' => 'Czech Republic Koruna',
				'DKK' => 'Denmark Krone',
				'DOP' => 'Dominican Republic Peso',
				'XCD' => 'East Caribbean Dollar',
				'EGP' => 'Egypt Pound',
				'SVC' => 'El Salvador Colon',
				'EEK' => 'Estonia Kroon',
				'EUR' => 'Euro Member Countries',
				'FKP' => 'Falkland Islands (Malvinas) Pound',
				'FJD' => 'Fiji Dollar',
				'GHC' => 'Ghana Cedis',
				'GIP' => 'Gibraltar Pound',
				'GTQ' => 'Guatemala Quetzal',
				'GGP' => 'Guernsey Pound',
				'GYD' => 'Guyana Dollar',
				'HNL' => 'Honduras Lempira',
				'HKD' => 'Hong Kong Dollar',
				'HUF' => 'Hungary Forint',
				'ISK' => 'Iceland Krona',
				'INR' => 'India Rupee',
				'IDR' => 'Indonesia Rupiah',
				'IRR' => 'Iran Rial',
				'IMP' => 'Isle of Man Pound',
				'ILS' => 'Israel Shekel',
				'JMD' => 'Jamaica Dollar',
				'JPY' => 'Japan Yen',
				'JEP' => 'Jersey Pound',
				'KZT' => 'Kazakhstan Tenge',
				'KPW' => 'Korea (North) Won',
				'KRW' => 'Korea (South) Won',
				'KGS' => 'Kyrgyzstan Som',
				'LAK' => 'Laos Kip',
				'LVL' => 'Latvia Lat',
				'LBP' => 'Lebanon Pound',
				'LRD' => 'Liberia Dollar',
				'LTL' => 'Lithuania Litas',
				'MKD' => 'Macedonia Denar',
				'MYR' => 'Malaysia Ringgit',
				'MUR' => 'Mauritius Rupee',
				'MXN' => 'Mexico Peso',
				'MNT' => 'Mongolia Tughrik',
				'MZN' => 'Mozambique Metical',
				'NAD' => 'Namibia Dollar',
				'NPR' => 'Nepal Rupee',
				'ANG' => 'Netherlands Antilles Guilder',
				'NZD' => 'New Zealand Dollar',
				'NIO' => 'Nicaragua Cordoba',
				'NGN' => 'Nigeria Naira',
				'NOK' => 'Norway Krone',
				'OMR' => 'Oman Rial',
				'PKR' => 'Pakistan Rupee',
				'PAB' => 'Panama Balboa',
				'PYG' => 'Paraguay Guarani',
				'PEN' => 'Peru Nuevo Sol',
				'PHP' => 'Philippines Peso',
				'PLN' => 'Poland Zloty',
				'QAR' => 'Qatar Riyal',
				'RON' => 'Romania New Leu',
				'RUB' => 'Russia Ruble',
				'SHP' => 'Saint Helena Pound',
				'SAR' => 'Saudi Arabia Riyal',
				'RSD' => 'Serbia Dinar',
				'SCR' => 'Seychelles Rupee',
				'SGD' => 'Singapore Dollar',
				'SBD' => 'Solomon Islands Dollar',
				'SOS' => 'Somalia Shilling',
				'ZAR' => 'South Africa Rand',
				'LKR' => 'Sri Lanka Rupee',
				'SEK' => 'Sweden Krona',
				'CHF' => 'Switzerland Franc',
				'SRD' => 'Suriname Dollar',
				'SYP' => 'Syria Pound',
				'TWD' => 'Taiwan New Dollar',
				'THB' => 'Thailand Baht',
				'TTD' => 'Trinidad and Tobago Dollar',
				'TRY' => 'Turkey Lira',
				'TRL' => 'Turkey Lira',
				'TVD' => 'Tuvalu Dollar',
				'UAH' => 'Ukraine Hryvna',
				'GBP' => 'United Kingdom Pound',
				'USD' => 'United States Dollar',
				'UYU' => 'Uruguay Peso',
				'UZS' => 'Uzbekistan Som',
				'VEF' => 'Venezuela Bolivar',
				'VND' => 'Viet Nam Dong',
				'YER' => 'Yemen Rial',
				'ZWD' => 'Zimbabwe Dollar'
			];
		}

		public static function getWholeNumberCurrencies(): array {
			return [
				'BIF',
				'CLP',
				'DJF',
				'GNF',
				'JPY',
				'KMF',
				'KRW',
				'MGA',
				'PYG',
				'RWF',
				'UGX',
				'VND',
				'VUV',
				'XAF',
				'XOF',
				'XPF'
			];
		}
	}

}