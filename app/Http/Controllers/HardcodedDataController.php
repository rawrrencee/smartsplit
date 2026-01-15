<?php

namespace App\Http\Controllers;

class HardcodedDataController extends Controller
{
    public function getFrontendUtcDateTimeFormat()
    {
        return 'Y-m-d\TH:i:s.u\Z';
    }

    public function getTimezone()
    {
        return 'Asia/Singapore';
    }

    public function getSqlDateTimeFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public function getCategories()
    {
        $categories = [
            [
                'key' => 'food',
                'value' => 'Food',
            ],
            [
                'key' => 'transport',
                'value' => 'Transport',
            ],
            [
                'key' => 'entertainment',
                'value' => 'Entertainment',
            ],
            [
                'key' => 'utilities',
                'value' => 'Utilities',
            ],
            [
                'key' => 'groceries',
                'value' => 'Groceries',
            ],
            [
                'key' => 'shopping',
                'value' => 'Shopping',
            ],
            [
                'key' => 'restaurants',
                'value' => 'Restaurants',
            ],
            [
                'key' => 'travel',
                'value' => 'Travel',
            ],
            [
                'key' => 'rent',
                'value' => 'Rent',
            ],
            [
                'key' => 'healthcare',
                'value' => 'Healthcare',
            ],
            [
                'key' => 'gifts',
                'value' => 'Gifts',
            ],
            [
                'key' => 'electronics',
                'value' => 'Electronics',
            ],
            [
                'key' => 'insurance',
                'value' => 'Insurance',
            ],
            [
                'key' => 'fuel',
                'value' => 'Fuel',
            ],
            [
                'key' => 'education',
                'value' => 'Education',
            ],
        ];

        return $categories;
    }

    public function getCurrencies()
    {
        $currencies = [
            [
                'key' => 'SGD',
                'countryName' => 'Singapore',
                'value' => 'Singapore dollar',
                'symbol' => '&#83;&#36;',
            ],

            [
                'key' => 'MYR',
                'countryName' => 'Malaysia',
                'value' => 'Malaysian ringgit',
                'symbol' => '&#82;&#77;',
            ],

            [
                'key' => 'JPY',
                'countryName' => 'Japan',
                'value' => 'Japanese yen',
                'symbol' => '&#165;',
            ],

            [
                'key' => 'ALL',
                'countryName' => 'Albania',
                'value' => 'Albanian lek',
                'symbol' => 'L',
            ],

            [
                'key' => 'AFN',
                'countryName' => 'Afghanistan',
                'value' => 'Afghanistan Afghani',
                'symbol' => '&#1547;',
            ],

            [
                'key' => 'ARS',
                'countryName' => 'Argentina',
                'value' => 'Argentine Peso',
                'symbol' => '&#36;',
            ],

            [
                'key' => 'AWG',
                'countryName' => 'Aruba',
                'value' => 'Aruban florin',
                'symbol' => '&#402;',
            ],

            [
                'key' => 'AUD',
                'countryName' => 'Australia',
                'value' => 'Australian Dollar',
                'symbol' => '&#65;&#36;',
            ],

            [
                'key' => 'AZN',
                'countryName' => 'Azerbaijan',
                'value' => 'Azerbaijani Manat',
                'symbol' => '&#8380;',
            ],

            [
                'key' => 'BSD',
                'countryName' => 'The Bahamas',
                'value' => 'Bahamas Dollar',
                'symbol' => '&#66;&#36;',
            ],

            [
                'key' => 'BBD',
                'countryName' => 'Barbados',
                'value' => 'Barbados Dollar',
                'symbol' => '&#66;&#100;&#115;&#36;',
            ],

            [
                'key' => 'BDT',
                'countryName' => 'People\'s Republic of Bangladesh',
                'value' => 'Bangladeshi taka',
                'symbol' => '&#2547;',
            ],

            [
                'key' => 'BYN',
                'countryName' => 'Belarus',
                'value' => 'Belarus Ruble',
                'symbol' => '&#66;&#114;',
            ],

            [
                'key' => 'BZD',
                'countryName' => 'Belize',
                'value' => 'Belize Dollar',
                'symbol' => '&#66;&#90;&#36;',
            ],

            [
                'key' => 'BMD',
                'countryName' => 'British Overseas Territory of Bermuda',
                'value' => 'Bermudian Dollar',
                'symbol' => '&#66;&#68;&#36;',
            ],

            [
                'key' => 'BOP',
                'countryName' => 'Bolivia',
                'value' => 'Boliviano',
                'symbol' => '&#66;&#115;',
            ],

            [
                'key' => 'BAM',
                'countryName' => 'Bosnia and Herzegovina',
                'value' => 'Bosnia-Herzegovina Convertible Marka',
                'symbol' => '&#75;&#77;',
            ],

            [
                'key' => 'BWP',
                'countryName' => 'Botswana',
                'value' => 'Botswana pula',
                'symbol' => '&#80;',
            ],

            [
                'key' => 'BGN',
                'countryName' => 'Bulgaria',
                'value' => 'Bulgarian lev',
                'symbol' => '&#1083;&#1074;',
            ],

            [
                'key' => 'BRL',
                'countryName' => 'Brazil',
                'value' => 'Brazilian real',
                'symbol' => '&#82;&#36;',
            ],

            [
                'key' => 'BND',
                'countryName' => 'Sultanate of Brunei',
                'value' => 'Brunei dollar',
                'symbol' => '&#66;&#36;',
            ],

            [
                'key' => 'KHR',
                'countryName' => 'Cambodia',
                'value' => 'Cambodian riel',
                'symbol' => '&#6107;',
            ],

            [
                'key' => 'CAD',
                'countryName' => 'Canada',
                'value' => 'Canadian dollar',
                'symbol' => '&#67;&#36;',
            ],

            [
                'key' => 'KYD',
                'countryName' => 'Cayman Islands',
                'value' => 'Cayman Islands dollar',
                'symbol' => '&#36;',
            ],

            [
                'key' => 'CLP',
                'countryName' => 'Chile',
                'value' => 'Chilean peso',
                'symbol' => '&#36;',
            ],

            [
                'key' => 'CNY',
                'countryName' => 'China',
                'value' => 'Chinese Yuan Renminbi',
                'symbol' => '&#165;',
            ],

            [
                'key' => 'COP',
                'countryName' => 'Colombia',
                'value' => 'Colombian peso',
                'symbol' => '&#36;',
            ],

            [
                'key' => 'CRC',
                'countryName' => 'Costa Rica',
                'value' => 'Costa Rican colón',
                'symbol' => '&#8353;',
            ],

            [
                'key' => 'HRK',
                'countryName' => 'Croatia',
                'value' => 'Croatian kuna',
                'symbol' => '&#107;&#110;',
            ],

            [
                'key' => 'CUP',
                'countryName' => 'Cuba',
                'value' => 'Cuban peso',
                'symbol' => '&#8369;',
            ],

            [
                'key' => 'CZK',
                'countryName' => 'Czech Republic',
                'value' => 'Czech koruna',
                'symbol' => '&#75;&#269;',
            ],

            [
                'key' => 'DKK',
                'countryName' => 'Denmark, Greenland, and the Faroe Islands',
                'value' => 'Danish krone',
                'symbol' => '&#107;&#114;',
            ],

            [
                'key' => 'DOP',
                'countryName' => 'Dominican Republic',
                'value' => 'Dominican peso',
                'symbol' => '&#82;&#68;&#36;',
            ],

            [
                'key' => 'XCD',
                'countryName' => 'Antigua and Barbuda, Commonwealth of Dominica, Grenada, Montserrat, St. Kitts and Nevis, Saint Lucia and St. Vincent and the Grenadines',
                'value' => 'Eastern Caribbean dollar',
                'symbol' => '&#36;',
            ],

            [
                'key' => 'EGP',
                'countryName' => 'Egypt',
                'value' => 'Egyptian pound',
                'symbol' => '&#163;',
            ],

            [
                'key' => 'SVC',
                'countryName' => 'El Salvador',
                'value' => 'Salvadoran colón',
                'symbol' => '&#36;',
            ],

            [
                'key' => 'EEK',
                'countryName' => 'Estonia',
                'value' => 'Estonian kroon',
                'symbol' => '&#75;&#114;',
            ],

            [
                'key' => 'EUR',
                'countryName' => 'European Union, Italy, Belgium, Bulgaria, Croatia, Cyprus, Czechia, Denmark, Estonia, Finland, France, Germany, 
                    Greece, Hungary, Ireland, Latvia, Lithuania, Luxembourg, Malta, Netherlands, Poland, 
                    Portugal, Romania, Slovakia, Slovenia, Spain, Sweden',
                'value' => 'Euro',
                'symbol' => '&#8364;',
            ],

            [
                'key' => 'FKP',
                'countryName' => 'Falkland Islands',
                'value' => 'Falkland Islands (Malvinas) Pound',
                'symbol' => '&#70;&#75;&#163;',
            ],

            [
                'key' => 'FJD',
                'countryName' => 'Fiji',
                'value' => 'Fijian dollar',
                'symbol' => '&#70;&#74;&#36;',
            ],

            [
                'key' => 'GHC',
                'countryName' => 'Ghana',
                'value' => 'Ghanaian cedi',
                'symbol' => '&#71;&#72;&#162;',
            ],

            [
                'key' => 'GIP',
                'countryName' => 'Gibraltar',
                'value' => 'Gibraltar pound',
                'symbol' => '&#163;',
            ],

            [
                'key' => 'GTQ',
                'countryName' => 'Guatemala',
                'value' => 'Guatemalan quetzal',
                'symbol' => '&#81;',
            ],

            [
                'key' => 'GGP',
                'countryName' => 'Guernsey',
                'value' => 'Guernsey pound',
                'symbol' => '&#81;',
            ],

            [
                'key' => 'GYD',
                'countryName' => 'Guyana',
                'value' => 'Guyanese dollar',
                'symbol' => '&#71;&#89;&#36;',
            ],

            [
                'key' => 'HNL',
                'countryName' => 'Honduras',
                'value' => 'Honduran lempira',
                'symbol' => '&#76;',
            ],

            [
                'key' => 'HKD',
                'countryName' => 'Hong Kong',
                'value' => 'Hong Kong dollar',
                'symbol' => '&#72;&#75;&#36;',
            ],

            [
                'key' => 'HUF',
                'countryName' => 'Hungary',
                'value' => 'Hungarian forint',
                'symbol' => '&#70;&#116;',
            ],

            [
                'key' => 'ISK',
                'countryName' => 'Iceland',
                'value' => 'Icelandic króna',
                'symbol' => '&#237;&#107;&#114;',
            ],

            [
                'key' => 'INR',
                'countryName' => 'India',
                'value' => 'Indian rupee',
                'symbol' => '&#8377;',
            ],

            [
                'key' => 'IDR',
                'countryName' => 'Indonesia',
                'value' => 'Indonesian rupiah',
                'symbol' => '&#82;&#112;',
            ],

            [
                'key' => 'IRR',
                'countryName' => 'Iran',
                'value' => 'Iranian rial',
                'symbol' => '&#65020;',
            ],

            [
                'key' => 'IMP',
                'countryName' => 'Isle of Man',
                'value' => 'Manx pound',
                'symbol' => '&#163;',
            ],

            [
                'key' => 'ILS',
                'countryName' => 'Israel, Palestinian territories of the West Bank and the Gaza Strip',
                'value' => 'Israeli Shekel',
                'symbol' => '&#8362;',
            ],

            [
                'key' => 'JMD',
                'countryName' => 'Jamaica',
                'value' => 'Jamaican dollar',
                'symbol' => '&#74;&#36;',
            ],

            [
                'key' => 'JEP',
                'countryName' => 'Jersey',
                'value' => 'Jersey pound',
                'symbol' => '&#163;',
            ],

            [
                'key' => 'KZT',
                'countryName' => 'Kazakhstan',
                'value' => 'Kazakhstani tenge',
                'symbol' => '&#8376;',
            ],

            [
                'key' => 'KRW',
                'countryName' => 'North Korea',
                'value' => 'North Korean won',
                'symbol' => '&#8361;',
            ],

            [
                'key' => 'KRW',
                'countryName' => 'South Korea',
                'value' => 'South Korean won',
                'symbol' => '&#8361;',
            ],

            [
                'key' => 'KGS',
                'countryName' => 'Kyrgyz Republic',
                'value' => 'Kyrgyzstani som',
                'symbol' => '&#1083;&#1074;',
            ],

            [
                'key' => 'LAK',
                'countryName' => 'Laos',
                'value' => 'Lao kip',
                'symbol' => '&#8365;',
            ],

            [
                'key' => 'LAK',
                'countryName' => 'Laos',
                'value' => 'Latvian lats',
                'symbol' => '&#8364;',
            ],

            [
                'key' => 'LVL',
                'countryName' => 'Laos',
                'value' => 'Latvian lats',
                'symbol' => '&#8364;',
            ],

            [
                'key' => 'LBP',
                'countryName' => 'Lebanon',
                'value' => 'Lebanese pound',
                'symbol' => '&#76;&#163;',
            ],

            [
                'key' => 'LRD',
                'countryName' => 'Liberia',
                'value' => 'Liberian dollar',
                'symbol' => '&#76;&#68;&#36;',
            ],

            [
                'key' => 'LTL',
                'countryName' => 'Lithuania',
                'value' => 'Lithuanian litas',
                'symbol' => '&#8364;',
            ],

            [
                'key' => 'MKD',
                'countryName' => 'North Macedonia',
                'value' => 'Macedonian denar',
                'symbol' => '&#1076;&#1077;&#1085;',
            ],

            [
                'key' => 'MUR',
                'countryName' => 'Mauritius',
                'value' => 'Mauritian rupee',
                'symbol' => '&#82;&#115;',
            ],

            [
                'key' => 'MXN',
                'countryName' => 'Mexico',
                'value' => 'Mexican peso',
                'symbol' => '&#77;&#101;&#120;&#36;',
            ],

            [
                'key' => 'MNT',
                'countryName' => 'Mongolia',
                'value' => 'Mongolian tögrög',
                'symbol' => '&#8366;',
            ],

            [
                'key' => 'MZN',
                'countryName' => 'Mozambique',
                'value' => 'Mozambican metical',
                'symbol' => '&#77;&#84;',
            ],

            [
                'key' => 'NAD',
                'countryName' => 'Namibia',
                'value' => 'Namibian dollar',
                'symbol' => '&#78;&#36;',
            ],

            [
                'key' => 'NPR',
                'countryName' => 'Federal Democratic Republic of Nepal',
                'value' => 'Nepalese rupee',
                'symbol' => '&#82;&#115;&#46;',
            ],

            [
                'key' => 'ANG',
                'countryName' => 'Curaçao and Sint Maarten',
                'value' => 'Netherlands Antillean guilder',
                'symbol' => '&#402;',
            ],

            [
                'key' => 'NZD',
                'countryName' => 'New Zealand, the Cook Islands, Niue, the Ross Dependency, Tokelau, the Pitcairn Islands',
                'value' => 'New Zealand dollar',
                'symbol' => '&#36;',
            ],

            [
                'key' => 'NIO',
                'countryName' => 'Nicaragua',
                'value' => 'Nicaraguan córdoba',
                'symbol' => '&#67;&#36;',
            ],

            [
                'key' => 'NGN',
                'countryName' => 'Nigeria',
                'value' => 'Nigerian naira',
                'symbol' => '&#8358;',
            ],

            [
                'key' => 'NOK',
                'countryName' => 'Norway and its dependent territories',
                'value' => 'Norwegian krone',
                'symbol' => '&#107;&#114;',
            ],

            [
                'key' => 'OMR',
                'countryName' => 'Oman',
                'value' => 'Omani rial',
                'symbol' => '&#65020;',
            ],

            [
                'key' => 'PKR',
                'countryName' => 'Pakistan',
                'value' => 'Pakistani rupee',
                'symbol' => '&#82;&#115;',
            ],

            [
                'key' => 'PAB',
                'countryName' => 'Panama',
                'value' => 'Panamanian balboa',
                'symbol' => '&#66;&#47;&#46;',
            ],

            [
                'key' => 'PYG',
                'countryName' => 'Paraguay',
                'value' => 'Paraguayan Guaraní',
                'symbol' => '&#8370;',
            ],

            [
                'key' => 'PEN',
                'countryName' => 'Peru',
                'value' => 'Sol',
                'symbol' => '&#83;&#47;&#46;',
            ],

            [
                'key' => 'PHP',
                'countryName' => 'Philippines',
                'value' => 'Philippine peso',
                'symbol' => '&#8369;',
            ],

            [
                'key' => 'PLN',
                'countryName' => 'Poland',
                'value' => 'Polish złoty',
                'symbol' => '&#122;&#322;',
            ],

            [
                'key' => 'QAR',
                'countryName' => 'State of Qatar',
                'value' => 'Qatari Riyal',
                'symbol' => '&#65020;',
            ],

            [
                'key' => 'RON',
                'countryName' => 'Romania',
                'value' => 'Romanian leu (Leu românesc)',
                'symbol' => '&#76;',
            ],

            [
                'key' => 'RUB',
                'countryName' => 'Russian Federation, Abkhazia and South Ossetia, Donetsk and Luhansk',
                'value' => 'Russian ruble',
                'symbol' => '&#8381;',
            ],

            [
                'key' => 'SHP',
                'countryName' => 'Saint Helena, Ascension and Tristan da Cunha',
                'value' => 'Saint Helena pound',
                'symbol' => '&#163;',
            ],

            [
                'key' => 'SAR',
                'countryName' => 'Saudi Arabia',
                'value' => 'Saudi riyal',
                'symbol' => '&#65020;',
            ],

            [
                'key' => 'RSD',
                'countryName' => 'Serbia',
                'value' => 'Serbian dinar',
                'symbol' => '&#100;&#105;&#110;',
            ],

            [
                'key' => 'SCR',
                'countryName' => 'Seychelles',
                'value' => 'Seychellois rupee',
                'symbol' => '&#82;&#115;',
            ],

            [
                'key' => 'SBD',
                'countryName' => 'Solomon Islands',
                'value' => 'Solomon Islands dollar',
                'symbol' => '&#83;&#73;&#36;',
            ],

            [
                'key' => 'SOS',
                'countryName' => 'Somalia',
                'value' => 'Somali shilling',
                'symbol' => '&#83;&#104;&#46;&#83;&#111;',
            ],

            [
                'key' => 'ZAR',
                'countryName' => 'South Africa',
                'value' => 'South African rand',
                'symbol' => '&#82;',
            ],

            [
                'key' => 'LKR',
                'countryName' => 'Sri Lanka',
                'value' => 'Sri Lankan rupee',
                'symbol' => '&#82;&#115;',
            ],

            [
                'key' => 'SEK',
                'countryName' => 'Sweden',
                'value' => 'Swedish krona',
                'symbol' => '&#107;&#114;',
            ],

            [
                'key' => 'CHF',
                'countryName' => 'Switzerland',
                'value' => 'Swiss franc',
                'symbol' => '&#67;&#72;&#102;',
            ],

            [
                'key' => 'SRD',
                'countryName' => 'Suriname',
                'value' => 'Suriname Dollar',
                'symbol' => '&#83;&#114;&#36;',
            ],

            [
                'key' => 'SYP',
                'countryName' => 'Syria',
                'value' => 'Syrian pound',
                'symbol' => '&#163;&#83;',
            ],

            [
                'key' => 'TWD',
                'countryName' => 'Taiwan',
                'value' => 'New Taiwan dollar',
                'symbol' => '&#78;&#84;&#36;',
            ],

            [
                'key' => 'THB',
                'countryName' => 'Thailand',
                'value' => 'Thai baht',
                'symbol' => '&#3647;',
            ],

            [
                'key' => 'TTD',
                'countryName' => 'Trinidad and Tobago',
                'value' => 'Trinidad and Tobago dollar',
                'symbol' => '&#84;&#84;&#36;',
            ],

            [
                'key' => 'TRY',
                'countryName' => 'Turkey, Turkish Republic of Northern Cyprus',
                'value' => 'Turkey Lira',
                'symbol' => '&#8378;',
            ],

            [
                'key' => 'TVD',
                'countryName' => 'Tuvalu',
                'value' => 'Tuvaluan dollar',
                'symbol' => '&#84;&#86;&#36;',
            ],

            [
                'key' => 'UAH',
                'countryName' => 'Ukraine',
                'value' => 'Ukrainian hryvnia',
                'symbol' => '&#8372;',
            ],

            [
                'key' => 'GBP',
                'countryName' => 'United Kingdom, Jersey, Guernsey, the Isle of Man, Gibraltar, South Georgia and the South Sandwich Islands, the British Antarctic Territory, and Tristan da Cunha',
                'value' => 'Pound sterling',
                'symbol' => '&#163;',
            ],

            [
                'key' => 'UGX',
                'countryName' => 'Uganda',
                'value' => 'Ugandan shilling',
                'symbol' => '&#85;&#83;&#104;',
            ],

            [
                'key' => 'USD',
                'countryName' => 'United States',
                'value' => 'United States dollar',
                'symbol' => '&#36;',
            ],

            [
                'key' => 'UYU',
                'countryName' => 'Uruguayan',
                'value' => 'Peso Uruguayolar',
                'symbol' => '&#36;&#85;',
            ],

            [
                'key' => 'UZS',
                'countryName' => 'Uzbekistan',
                'value' => 'Uzbekistani soʻm',
                'symbol' => '&#1083;&#1074;',
            ],

            [
                'key' => 'VEF',
                'countryName' => 'Venezuela',
                'value' => 'Venezuelan bolívar',
                'symbol' => '&#66;&#115;',
            ],

            [
                'key' => 'VND',
                'countryName' => 'Vietnam',
                'value' => 'Vietnamese dong (Đồng)',
                'symbol' => '&#8363;',
            ],

            [
                'key' => 'VND',
                'countryName' => 'Yemen',
                'value' => 'Yemeni rial',
                'symbol' => '&#65020;',
            ],

            [
                'key' => 'ZWD',
                'countryName' => 'Zimbabwe',
                'value' => 'Zimbabwean dollar',
                'symbol' => '&#90;&#36;',
            ],
        ];

        foreach ($currencies as &$currency) {
            $currency['symbol'] = html_entity_decode($currency['symbol']);
        }

        return $currencies;
    }
}
