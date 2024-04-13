<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

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
        $categories = array(
            array(
                'key' => 'food',
                'value' => 'Food'
            ),
            array(
                'key' => 'transport',
                'value' => 'Transport'
            ),
            array(
                'key' => 'entertainment',
                'value' => 'Entertainment'
            ),
            array(
                'key' => 'utilities',
                'value' => 'Utilities'
            ),
            array(
                'key' => 'groceries',
                'value' => 'Groceries'
            ),
            array(
                'key' => 'shopping',
                'value' => 'Shopping'
            ),
            array(
                'key' => 'restaurants',
                'value' => 'Restaurants'
            ),
            array(
                'key' => 'travel',
                'value' => 'Travel'
            ),
            array(
                'key' => 'rent',
                'value' => 'Rent'
            ),
            array(
                'key' => 'healthcare',
                'value' => 'Healthcare'
            ),
            array(
                'key' => 'gifts',
                'value' => 'Gifts'
            ),
            array(
                'key' => 'electronics',
                'value' => 'Electronics'
            ),
            array(
                'key' => 'insurance',
                'value' => 'Insurance'
            ),
            array(
                'key' => 'fuel',
                'value' => 'Fuel'
            ),
            array(
                'key' => 'education',
                'value' => 'Education'
            )
        );

        return $categories;
    }

    public function getCurrencies()
    {
        $currencies = array(
            array(
                'key' => 'SGD',
                'countryName' => 'Singapore',
                'value' => 'Singapore dollar',
                'symbol' => '&#83;&#36;'
            ),

            array(
                'key' => 'MYR',
                'countryName' => 'Malaysia',
                'value' => 'Malaysian ringgit',
                'symbol' => '&#82;&#77;'
            ),

            array(
                'key' => 'JPY',
                'countryName' => 'Japan',
                'value' => 'Japanese yen',
                'symbol' => '&#165;'
            ),

            array(
                'key' => 'ALL',
                'countryName' => 'Albania',
                'value' => 'Albanian lek',
                'symbol' => 'L'
            ),

            array(
                'key' => 'AFN',
                'countryName' => 'Afghanistan',
                'value' => 'Afghanistan Afghani',
                'symbol' => '&#1547;'
            ),

            array(
                'key' => 'ARS',
                'countryName' => 'Argentina',
                'value' => 'Argentine Peso',
                'symbol' => '&#36;'
            ),

            array(
                'key' => 'AWG',
                'countryName' => 'Aruba',
                'value' => 'Aruban florin',
                'symbol' => '&#402;'
            ),

            array(
                'key' => 'AUD',
                'countryName' => 'Australia',
                'value' => 'Australian Dollar',
                'symbol' => '&#65;&#36;'
            ),

            array(
                'key' => 'AZN',
                'countryName' => 'Azerbaijan',
                'value' => 'Azerbaijani Manat',
                'symbol' => '&#8380;'
            ),

            array(
                'key' => 'BSD',
                'countryName' => 'The Bahamas',
                'value' => 'Bahamas Dollar',
                'symbol' => '&#66;&#36;'
            ),

            array(
                'key' => 'BBD',
                'countryName' => 'Barbados',
                'value' => 'Barbados Dollar',
                'symbol' => '&#66;&#100;&#115;&#36;'
            ),

            array(
                'key' => 'BDT',
                'countryName' => 'People\'s Republic of Bangladesh',
                'value' => 'Bangladeshi taka',
                'symbol' => '&#2547;'
            ),

            array(
                'key' => 'BYN',
                'countryName' => 'Belarus',
                'value' => 'Belarus Ruble',
                'symbol' => '&#66;&#114;'
            ),

            array(
                'key' => 'BZD',
                'countryName' => 'Belize',
                'value' => 'Belize Dollar',
                'symbol' => '&#66;&#90;&#36;'
            ),

            array(
                'key' => 'BMD',
                'countryName' => 'British Overseas Territory of Bermuda',
                'value' => 'Bermudian Dollar',
                'symbol' => '&#66;&#68;&#36;'
            ),

            array(
                'key' => 'BOP',
                'countryName' => 'Bolivia',
                'value' => 'Boliviano',
                'symbol' => '&#66;&#115;'
            ),

            array(
                'key' => 'BAM',
                'countryName' => 'Bosnia and Herzegovina',
                'value' => 'Bosnia-Herzegovina Convertible Marka',
                'symbol' => '&#75;&#77;'
            ),

            array(
                'key' => 'BWP',
                'countryName' => 'Botswana',
                'value' => 'Botswana pula',
                'symbol' => '&#80;'
            ),

            array(
                'key' => 'BGN',
                'countryName' => 'Bulgaria',
                'value' => 'Bulgarian lev',
                'symbol' => '&#1083;&#1074;'
            ),

            array(
                'key' => 'BRL',
                'countryName' => 'Brazil',
                'value' => 'Brazilian real',
                'symbol' => '&#82;&#36;'
            ),

            array(
                'key' => 'BND',
                'countryName' => 'Sultanate of Brunei',
                'value' => 'Brunei dollar',
                'symbol' => '&#66;&#36;'
            ),

            array(
                'key' => 'KHR',
                'countryName' => 'Cambodia',
                'value' => 'Cambodian riel',
                'symbol' => '&#6107;'
            ),

            array(
                'key' => 'CAD',
                'countryName' => 'Canada',
                'value' => 'Canadian dollar',
                'symbol' => '&#67;&#36;'
            ),

            array(
                'key' => 'KYD',
                'countryName' => 'Cayman Islands',
                'value' => 'Cayman Islands dollar',
                'symbol' => '&#36;'
            ),

            array(
                'key' => 'CLP',
                'countryName' => 'Chile',
                'value' => 'Chilean peso',
                'symbol' => '&#36;'
            ),

            array(
                'key' => 'CNY',
                'countryName' => 'China',
                'value' => 'Chinese Yuan Renminbi',
                'symbol' => '&#165;'
            ),

            array(
                'key' => 'COP',
                'countryName' => 'Colombia',
                'value' => 'Colombian peso',
                'symbol' => '&#36;'
            ),

            array(
                'key' => 'CRC',
                'countryName' => 'Costa Rica',
                'value' => 'Costa Rican colón',
                'symbol' => '&#8353;'
            ),

            array(
                'key' => 'HRK',
                'countryName' => 'Croatia',
                'value' => 'Croatian kuna',
                'symbol' => '&#107;&#110;'
            ),

            array(
                'key' => 'CUP',
                'countryName' => 'Cuba',
                'value' => 'Cuban peso',
                'symbol' => '&#8369;'
            ),

            array(
                'key' => 'CZK',
                'countryName' => 'Czech Republic',
                'value' => 'Czech koruna',
                'symbol' => '&#75;&#269;'
            ),

            array(
                'key' => 'DKK',
                'countryName' => 'Denmark, Greenland, and the Faroe Islands',
                'value' => 'Danish krone',
                'symbol' => '&#107;&#114;'
            ),

            array(
                'key' => 'DOP',
                'countryName' => 'Dominican Republic',
                'value' => 'Dominican peso',
                'symbol' => '&#82;&#68;&#36;'
            ),

            array(
                'key' => 'XCD',
                'countryName' => 'Antigua and Barbuda, Commonwealth of Dominica, Grenada, Montserrat, St. Kitts and Nevis, Saint Lucia and St. Vincent and the Grenadines',
                'value' => 'Eastern Caribbean dollar',
                'symbol' => '&#36;'
            ),

            array(
                'key' => 'EGP',
                'countryName' => 'Egypt',
                'value' => 'Egyptian pound',
                'symbol' => '&#163;'
            ),

            array(
                'key' => 'SVC',
                'countryName' => 'El Salvador',
                'value' => 'Salvadoran colón',
                'symbol' => '&#36;'
            ),

            array(
                'key' => 'EEK',
                'countryName' => 'Estonia',
                'value' => 'Estonian kroon',
                'symbol' => '&#75;&#114;'
            ),

            array(
                'key' => 'EUR',
                'countryName' => 'European Union, Italy, Belgium, Bulgaria, Croatia, Cyprus, Czechia, Denmark, Estonia, Finland, France, Germany, 
                    Greece, Hungary, Ireland, Latvia, Lithuania, Luxembourg, Malta, Netherlands, Poland, 
                    Portugal, Romania, Slovakia, Slovenia, Spain, Sweden',
                'value' => 'Euro',
                'symbol' => '&#8364;'
            ),

            array(
                'key' => 'FKP',
                'countryName' => 'Falkland Islands',
                'value' => 'Falkland Islands (Malvinas) Pound',
                'symbol' => '&#70;&#75;&#163;'
            ),

            array(
                'key' => 'FJD',
                'countryName' => 'Fiji',
                'value' => 'Fijian dollar',
                'symbol' => '&#70;&#74;&#36;'
            ),

            array(
                'key' => 'GHC',
                'countryName' => 'Ghana',
                'value' => 'Ghanaian cedi',
                'symbol' => '&#71;&#72;&#162;'
            ),

            array(
                'key' => 'GIP',
                'countryName' => 'Gibraltar',
                'value' => 'Gibraltar pound',
                'symbol' => '&#163;'
            ),

            array(
                'key' => 'GTQ',
                'countryName' => 'Guatemala',
                'value' => 'Guatemalan quetzal',
                'symbol' => '&#81;'
            ),

            array(
                'key' => 'GGP',
                'countryName' => 'Guernsey',
                'value' => 'Guernsey pound',
                'symbol' => '&#81;'
            ),

            array(
                'key' => 'GYD',
                'countryName' => 'Guyana',
                'value' => 'Guyanese dollar',
                'symbol' => '&#71;&#89;&#36;'
            ),

            array(
                'key' => 'HNL',
                'countryName' => 'Honduras',
                'value' => 'Honduran lempira',
                'symbol' => '&#76;'
            ),

            array(
                'key' => 'HKD',
                'countryName' => 'Hong Kong',
                'value' => 'Hong Kong dollar',
                'symbol' => '&#72;&#75;&#36;'
            ),

            array(
                'key' => 'HUF',
                'countryName' => 'Hungary',
                'value' => 'Hungarian forint',
                'symbol' => '&#70;&#116;'
            ),

            array(
                'key' => 'ISK',
                'countryName' => 'Iceland',
                'value' => 'Icelandic króna',
                'symbol' => '&#237;&#107;&#114;'
            ),

            array(
                'key' => 'INR',
                'countryName' => 'India',
                'value' => 'Indian rupee',
                'symbol' => '&#8377;'
            ),

            array(
                'key' => 'IDR',
                'countryName' => 'Indonesia',
                'value' => 'Indonesian rupiah',
                'symbol' => '&#82;&#112;'
            ),

            array(
                'key' => 'IRR',
                'countryName' => 'Iran',
                'value' => 'Iranian rial',
                'symbol' => '&#65020;'
            ),

            array(
                'key' => 'IMP',
                'countryName' => 'Isle of Man',
                'value' => 'Manx pound',
                'symbol' => '&#163;'
            ),

            array(
                'key' => 'ILS',
                'countryName' => 'Israel, Palestinian territories of the West Bank and the Gaza Strip',
                'value' => 'Israeli Shekel',
                'symbol' => '&#8362;'
            ),

            array(
                'key' => 'JMD',
                'countryName' => 'Jamaica',
                'value' => 'Jamaican dollar',
                'symbol' => '&#74;&#36;'
            ),

            array(
                'key' => 'JEP',
                'countryName' => 'Jersey',
                'value' => 'Jersey pound',
                'symbol' => '&#163;'
            ),

            array(
                'key' => 'KZT',
                'countryName' => 'Kazakhstan',
                'value' => 'Kazakhstani tenge',
                'symbol' => '&#8376;'
            ),

            array(
                'key' => 'KRW',
                'countryName' => 'North Korea',
                'value' => 'North Korean won',
                'symbol' => '&#8361;'
            ),

            array(
                'key' => 'KRW',
                'countryName' => 'South Korea',
                'value' => 'South Korean won',
                'symbol' => '&#8361;'
            ),

            array(
                'key' => 'KGS',
                'countryName' => 'Kyrgyz Republic',
                'value' => 'Kyrgyzstani som',
                'symbol' => '&#1083;&#1074;'
            ),

            array(
                'key' => 'LAK',
                'countryName' => 'Laos',
                'value' => 'Lao kip',
                'symbol' => '&#8365;'
            ),

            array(
                'key' => 'LAK',
                'countryName' => 'Laos',
                'value' => 'Latvian lats',
                'symbol' => '&#8364;'
            ),

            array(
                'key' => 'LVL',
                'countryName' => 'Laos',
                'value' => 'Latvian lats',
                'symbol' => '&#8364;'
            ),

            array(
                'key' => 'LBP',
                'countryName' => 'Lebanon',
                'value' => 'Lebanese pound',
                'symbol' => '&#76;&#163;'
            ),

            array(
                'key' => 'LRD',
                'countryName' => 'Liberia',
                'value' => 'Liberian dollar',
                'symbol' => '&#76;&#68;&#36;'
            ),

            array(
                'key' => 'LTL',
                'countryName' => 'Lithuania',
                'value' => 'Lithuanian litas',
                'symbol' => '&#8364;'
            ),

            array(
                'key' => 'MKD',
                'countryName' => 'North Macedonia',
                'value' => 'Macedonian denar',
                'symbol' => '&#1076;&#1077;&#1085;'
            ),

            array(
                'key' => 'MUR',
                'countryName' => 'Mauritius',
                'value' => 'Mauritian rupee',
                'symbol' => '&#82;&#115;'
            ),

            array(
                'key' => 'MXN',
                'countryName' => 'Mexico',
                'value' => 'Mexican peso',
                'symbol' => '&#77;&#101;&#120;&#36;'
            ),

            array(
                'key' => 'MNT',
                'countryName' => 'Mongolia',
                'value' => 'Mongolian tögrög',
                'symbol' => '&#8366;'
            ),


            array(
                'key' => 'MZN',
                'countryName' => 'Mozambique',
                'value' => 'Mozambican metical',
                'symbol' => '&#77;&#84;'
            ),

            array(
                'key' => 'NAD',
                'countryName' => 'Namibia',
                'value' => 'Namibian dollar',
                'symbol' => '&#78;&#36;'
            ),

            array(
                'key' => 'NPR',
                'countryName' => 'Federal Democratic Republic of Nepal',
                'value' => 'Nepalese rupee',
                'symbol' => '&#82;&#115;&#46;'
            ),

            array(
                'key' => 'ANG',
                'countryName' => 'Curaçao and Sint Maarten',
                'value' => 'Netherlands Antillean guilder',
                'symbol' => '&#402;'
            ),

            array(
                'key' => 'NZD',
                'countryName' => 'New Zealand, the Cook Islands, Niue, the Ross Dependency, Tokelau, the Pitcairn Islands',
                'value' => 'New Zealand dollar',
                'symbol' => '&#36;'
            ),


            array(
                'key' => 'NIO',
                'countryName' => 'Nicaragua',
                'value' => 'Nicaraguan córdoba',
                'symbol' => '&#67;&#36;'
            ),

            array(
                'key' => 'NGN',
                'countryName' => 'Nigeria',
                'value' => 'Nigerian naira',
                'symbol' => '&#8358;'
            ),

            array(
                'key' => 'NOK',
                'countryName' => 'Norway and its dependent territories',
                'value' => 'Norwegian krone',
                'symbol' => '&#107;&#114;'
            ),

            array(
                'key' => 'OMR',
                'countryName' => 'Oman',
                'value' => 'Omani rial',
                'symbol' => '&#65020;'
            ),

            array(
                'key' => 'PKR',
                'countryName' => 'Pakistan',
                'value' => 'Pakistani rupee',
                'symbol' => '&#82;&#115;'
            ),

            array(
                'key' => 'PAB',
                'countryName' => 'Panama',
                'value' => 'Panamanian balboa',
                'symbol' => '&#66;&#47;&#46;'
            ),

            array(
                'key' => 'PYG',
                'countryName' => 'Paraguay',
                'value' => 'Paraguayan Guaraní',
                'symbol' => '&#8370;'
            ),

            array(
                'key' => 'PEN',
                'countryName' => 'Peru',
                'value' => 'Sol',
                'symbol' => '&#83;&#47;&#46;'
            ),

            array(
                'key' => 'PHP',
                'countryName' => 'Philippines',
                'value' => 'Philippine peso',
                'symbol' => '&#8369;'
            ),

            array(
                'key' => 'PLN',
                'countryName' => 'Poland',
                'value' => 'Polish złoty',
                'symbol' => '&#122;&#322;'
            ),

            array(
                'key' => 'QAR',
                'countryName' => 'State of Qatar',
                'value' => 'Qatari Riyal',
                'symbol' => '&#65020;'
            ),

            array(
                'key' => 'RON',
                'countryName' => 'Romania',
                'value' => 'Romanian leu (Leu românesc)',
                'symbol' => '&#76;'
            ),

            array(
                'key' => 'RUB',
                'countryName' => 'Russian Federation, Abkhazia and South Ossetia, Donetsk and Luhansk',
                'value' => 'Russian ruble',
                'symbol' => '&#8381;'
            ),


            array(
                'key' => 'SHP',
                'countryName' => 'Saint Helena, Ascension and Tristan da Cunha',
                'value' => 'Saint Helena pound',
                'symbol' => '&#163;'
            ),

            array(
                'key' => 'SAR',
                'countryName' => 'Saudi Arabia',
                'value' => 'Saudi riyal',
                'symbol' => '&#65020;'
            ),

            array(
                'key' => 'RSD',
                'countryName' => 'Serbia',
                'value' => 'Serbian dinar',
                'symbol' => '&#100;&#105;&#110;'
            ),

            array(
                'key' => 'SCR',
                'countryName' => 'Seychelles',
                'value' => 'Seychellois rupee',
                'symbol' => '&#82;&#115;'
            ),

            array(
                'key' => 'SBD',
                'countryName' => 'Solomon Islands',
                'value' => 'Solomon Islands dollar',
                'symbol' => '&#83;&#73;&#36;'
            ),

            array(
                'key' => 'SOS',
                'countryName' => 'Somalia',
                'value' => 'Somali shilling',
                'symbol' => '&#83;&#104;&#46;&#83;&#111;'
            ),

            array(
                'key' => 'ZAR',
                'countryName' => 'South Africa',
                'value' => 'South African rand',
                'symbol' => '&#82;'
            ),

            array(
                'key' => 'LKR',
                'countryName' => 'Sri Lanka',
                'value' => 'Sri Lankan rupee',
                'symbol' => '&#82;&#115;'
            ),


            array(
                'key' => 'SEK',
                'countryName' => 'Sweden',
                'value' => 'Swedish krona',
                'symbol' => '&#107;&#114;'
            ),


            array(
                'key' => 'CHF',
                'countryName' => 'Switzerland',
                'value' => 'Swiss franc',
                'symbol' => '&#67;&#72;&#102;'
            ),

            array(
                'key' => 'SRD',
                'countryName' => 'Suriname',
                'value' => 'Suriname Dollar',
                'symbol' => '&#83;&#114;&#36;'
            ),

            array(
                'key' => 'SYP',
                'countryName' => 'Syria',
                'value' => 'Syrian pound',
                'symbol' => '&#163;&#83;'
            ),

            array(
                'key' => 'TWD',
                'countryName' => 'Taiwan',
                'value' => 'New Taiwan dollar',
                'symbol' => '&#78;&#84;&#36;'
            ),


            array(
                'key' => 'THB',
                'countryName' => 'Thailand',
                'value' => 'Thai baht',
                'symbol' => '&#3647;'
            ),


            array(
                'key' => 'TTD',
                'countryName' => 'Trinidad and Tobago',
                'value' => 'Trinidad and Tobago dollar',
                'symbol' => '&#84;&#84;&#36;'
            ),


            array(
                'key' => 'TRY',
                'countryName' => 'Turkey, Turkish Republic of Northern Cyprus',
                'value' => 'Turkey Lira',
                'symbol' => '&#8378;'
            ),

            array(
                'key' => 'TVD',
                'countryName' => 'Tuvalu',
                'value' => 'Tuvaluan dollar',
                'symbol' => '&#84;&#86;&#36;'
            ),

            array(
                'key' => 'UAH',
                'countryName' => 'Ukraine',
                'value' => 'Ukrainian hryvnia',
                'symbol' => '&#8372;'
            ),


            array(
                'key' => 'GBP',
                'countryName' => 'United Kingdom, Jersey, Guernsey, the Isle of Man, Gibraltar, South Georgia and the South Sandwich Islands, the British Antarctic Territory, and Tristan da Cunha',
                'value' => 'Pound sterling',
                'symbol' => '&#163;'
            ),


            array(
                'key' => 'UGX',
                'countryName' => 'Uganda',
                'value' => 'Ugandan shilling',
                'symbol' => '&#85;&#83;&#104;'
            ),


            array(
                'key' => 'USD',
                'countryName' => 'United States',
                'value' => 'United States dollar',
                'symbol' => '&#36;'
            ),

            array(
                'key' => 'UYU',
                'countryName' => 'Uruguayan',
                'value' => 'Peso Uruguayolar',
                'symbol' => '&#36;&#85;'
            ),

            array(
                'key' => 'UZS',
                'countryName' => 'Uzbekistan',
                'value' => 'Uzbekistani soʻm',
                'symbol' => '&#1083;&#1074;'
            ),


            array(
                'key' => 'VEF',
                'countryName' => 'Venezuela',
                'value' => 'Venezuelan bolívar',
                'symbol' => '&#66;&#115;'
            ),


            array(
                'key' => 'VND',
                'countryName' => 'Vietnam',
                'value' => 'Vietnamese dong (Đồng)',
                'symbol' => '&#8363;'
            ),

            array(
                'key' => 'VND',
                'countryName' => 'Yemen',
                'value' => 'Yemeni rial',
                'symbol' => '&#65020;'
            ),

            array(
                'key' => 'ZWD',
                'countryName' => 'Zimbabwe',
                'value' => 'Zimbabwean dollar',
                'symbol' => '&#90;&#36;'
            ),
        );

        foreach ($currencies as &$currency) {
            $currency['symbol'] = html_entity_decode($currency['symbol']);
        }

        return $currencies;
    }
}
