<?php

namespace TightenCo\Jigsaw\PathResolvers;

use Illuminate\Support\Arr;
use TightenCo\Jigsaw\IterableObject;

class CollectionPathResolver
{
    private $outputPathResolver;
    private $view;

    public function __construct($outputPathResolver, $viewRenderer)
    {
        $this->outputPathResolver = $outputPathResolver;
        $this->view = $viewRenderer;
    }

    public function link($path, $data)
    {
        return collect($data->extends)->map(function ($bladeViewPath, $templateKey) use ($path, $data) {
            return $this->cleanOutputPath(
                $this->getPath($path, $data, $this->getExtension($bladeViewPath), $templateKey)
            );
        });
    }

    public function getExtension($bladeViewPath)
    {
        $extension = $this->view->getExtension($bladeViewPath);

        return collect(['php', 'html'])->contains($extension) ? '' : '.' . $extension;
    }

    private function getPath($path, $data, $extension, $templateKey = null)
    {
        $templateKeySuffix = $templateKey ? '/' . $templateKey : '';

        if ($templateKey && $path instanceof IterableObject) {
            $path = $path->get($templateKey);
            $templateKeySuffix = '';

            if (! $path) {
                return;
            }
        }

        if (is_callable($path)) {
            $link = $this->cleanInputPath($path->__invoke($data));

            return $link ? $this->resolve($link . $templateKeySuffix . $extension) : '';
        }

        if (is_string($path) && $path) {
            $link = $this->parseShorthand($this->cleanInputPath($path), $data);

            return $link ? $this->resolve($link . $templateKeySuffix . $extension) : '';
        }

        return $this->getDefaultPath($data, $templateKey) . $templateKeySuffix . $extension;
    }

    private function getDefaultPath($data)
    {
        return $this->slug($data->getCollectionName()) . '/' . $this->slug($data->getFilename());
    }

    private function parseShorthand($path, $data)
    {
        preg_match_all('/\{(.*?)\}/', $path, $bracketedParameters);

        if (count($bracketedParameters[0]) == 0) {
            return $path . '/' . $this->slug($data->getFilename());
        }

        $bracketedParametersReplaced =
            collect($bracketedParameters[0])->map(function ($param) use ($data) {
                return ['token' => $param, 'value' => $this->getParameterValue($param, $data)];
            })->reduce(function ($carry, $param) use ($path) {
                return str_replace($param['token'], $param['value'], $carry);
            }, $path);

        return $bracketedParametersReplaced;
    }

    private function getParameterValue($param, $data)
    {
        list($param, $dateFormat) = explode('|', trim($param, '{}') . '|');
        $slugSeparator = ctype_alpha($param[0]) ? null : $param[0];

        if ($slugSeparator) {
            $param = ltrim($param, $param[0]);
        }

        $value = Arr::get($data, $param, $data->_meta->get($param));

        if (! $value) {
            return '';
        }

        $value = $dateFormat ? $this->formatDate($value, $dateFormat) : $value;

        return $slugSeparator ? $this->slug($value, $slugSeparator) : $value;
    }

    private function formatDate($date, $format)
    {
        if (is_string($date)) {
            return strtotime($date) ? date($format, strtotime($date)) : '';
        }

        return date($format, $date);
    }

    private function cleanInputPath($path)
    {
        return $this->ensureSlashAtBeginningOnly($path);
    }

    private function cleanOutputPath($path)
    {
        $removeDoubleSlashes = preg_replace('/\/\/+/', '/', $path);
        $transliterate = $this->ascii($removeDoubleSlashes);

        return $this->ensureSlashAtBeginningOnly($transliterate);
    }

    private function ensureSlashAtBeginningOnly($path)
    {
        return '/' . trimPath($path);
    }

    private function resolve($path)
    {
        return $this->outputPathResolver->link(dirname($path), basename($path), 'html');
    }

    /**
     * This is identical to Laravel's built-in `str_slug()` helper,
     * except it preserves `.` characters.
     */
    private function slug($string, $separator = '-')
    {
        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';
        $string = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $string);

        // Remove all characters that are not the separator, letters, numbers, whitespace, or dot
        $string = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s\.]+!u', '', mb_strtolower($string));

        // Replace all separator characters and whitespace by a single separator
        $string = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $string);

        return trim($string, $separator);
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param string $value
     * @param string $language
     *
     * @return string
     */
    private static function ascii($value, $language = 'en')
    {
        $languageSpecific = static::languageSpecificCharsArray($language);

        if (! is_null($languageSpecific)) {
            $value = str_replace($languageSpecific[0], $languageSpecific[1], $value);
        }

        foreach (static::charsArray() as $key => $val) {
            $value = str_replace($val, $key, $value);
        }

        return preg_replace('/[\x{00a1}-\x{00bf}|\x{2120}\x{2122}]/u', '', $value);
    }

    /**
     * Returns the replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/3.1.0/LICENSE.txt
     *
     * @return array
     */
    private static function charsArray()
    {
        static $charsArray;

        if (isset($charsArray)) {
            return $charsArray;
        }

        return $charsArray = [
            '0' => ['°', '₀', '۰', '０'],
            '1' => ['¹', '₁', '۱', '１'],
            '2' => ['²', '₂', '۲', '２'],
            '3' => ['³', '₃', '۳', '３'],
            '4' => ['⁴', '₄', '۴', '٤', '４'],
            '5' => ['⁵', '₅', '۵', '٥', '５'],
            '6' => ['⁶', '₆', '۶', '٦', '６'],
            '7' => ['⁷', '₇', '۷', '７'],
            '8' => ['⁸', '₈', '۸', '８'],
            '9' => ['⁹', '₉', '۹', '９'],
            'a' => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا', 'ａ', 'ä'],
            'b' => ['б', 'β', 'ب', 'ဗ', 'ბ', 'ｂ'],
            'c' => ['ç', 'ć', 'č', 'ĉ', 'ċ', 'ｃ'],
            'd' => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ', 'ｄ'],
            'e' => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ', 'ｅ'],
            'f' => ['ф', 'φ', 'ف', 'ƒ', 'ფ', 'ｆ'],
            'g' => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ', 'ｇ'],
            'h' => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ', 'ｈ'],
            'i' => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ', 'ی', 'ｉ'],
            'j' => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج', 'ｊ'],
            'k' => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک', 'ｋ'],
            'l' => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ', 'ｌ'],
            'm' => ['м', 'μ', 'م', 'မ', 'მ', 'ｍ'],
            'n' => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ', 'ｎ'],
            'o' => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ', 'ｏ', 'ö'],
            'p' => ['п', 'π', 'ပ', 'პ', 'پ', 'ｐ'],
            'q' => ['ყ', 'ｑ'],
            'r' => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ', 'ｒ'],
            's' => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს', 'ｓ'],
            't' => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ', 'ｔ'],
            'u' => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ', 'ｕ', 'ў', 'ü'],
            'v' => ['в', 'ვ', 'ϐ', 'ｖ'],
            'w' => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ', 'ｗ'],
            'x' => ['χ', 'ξ', 'ｘ'],
            'y' => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ', 'ｙ'],
            'z' => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ', 'ｚ'],
            'aa' => ['ع', 'आ', 'آ'],
            'ae' => ['æ', 'ǽ'],
            'ai' => ['ऐ'],
            'ch' => ['ч', 'ჩ', 'ჭ', 'چ'],
            'dj' => ['ђ', 'đ'],
            'dz' => ['џ', 'ძ'],
            'ei' => ['ऍ'],
            'gh' => ['غ', 'ღ'],
            'ii' => ['ई'],
            'ij' => ['ĳ'],
            'kh' => ['х', 'خ', 'ხ'],
            'lj' => ['љ'],
            'nj' => ['њ'],
            'oe' => ['ö', 'œ', 'ؤ'],
            'oi' => ['ऑ'],
            'oii' => ['ऒ'],
            'ps' => ['ψ'],
            'sh' => ['ш', 'შ', 'ش'],
            'shch' => ['щ'],
            'ss' => ['ß'],
            'sx' => ['ŝ'],
            'th' => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
            'ts' => ['ц', 'ც', 'წ'],
            'ue' => ['ü'],
            'uu' => ['ऊ'],
            'ya' => ['я'],
            'yu' => ['ю'],
            'zh' => ['ж', 'ჟ', 'ژ'],
            '(c)' => ['©'],
            'A' => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ', 'Ａ', 'Ä'],
            'B' => ['Б', 'Β', 'ब', 'Ｂ'],
            'C' => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ', 'Ｃ'],
            'D' => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ', 'Ｄ'],
            'E' => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə', 'Ｅ'],
            'F' => ['Ф', 'Φ', 'Ｆ'],
            'G' => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ', 'Ｇ'],
            'H' => ['Η', 'Ή', 'Ħ', 'Ｈ'],
            'I' => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ', 'Ｉ'],
            'J' => ['Ｊ'],
            'K' => ['К', 'Κ', 'Ｋ'],
            'L' => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल', 'Ｌ'],
            'M' => ['М', 'Μ', 'Ｍ'],
            'N' => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν', 'Ｎ'],
            'O' => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ', 'Ｏ', 'Ö'],
            'P' => ['П', 'Π', 'Ｐ'],
            'Q' => ['Ｑ'],
            'R' => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ', 'Ｒ'],
            'S' => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ', 'Ｓ'],
            'T' => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ', 'Ｔ'],
            'U' => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ', 'Ｕ', 'Ў', 'Ü'],
            'V' => ['В', 'Ｖ'],
            'W' => ['Ω', 'Ώ', 'Ŵ', 'Ｗ'],
            'X' => ['Χ', 'Ξ', 'Ｘ'],
            'Y' => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ', 'Ｙ'],
            'Z' => ['Ź', 'Ž', 'Ż', 'З', 'Ζ', 'Ｚ'],
            'AE' => ['Æ', 'Ǽ'],
            'Ch' => ['Ч'],
            'Dj' => ['Ђ'],
            'Dz' => ['Џ'],
            'Gx' => ['Ĝ'],
            'Hx' => ['Ĥ'],
            'Ij' => ['Ĳ'],
            'Jx' => ['Ĵ'],
            'Kh' => ['Х'],
            'Lj' => ['Љ'],
            'Nj' => ['Њ'],
            'Oe' => ['Œ'],
            'Ps' => ['Ψ'],
            'Sh' => ['Ш'],
            'Shch' => ['Щ'],
            'Ss' => ['ẞ'],
            'Th' => ['Þ'],
            'Ts' => ['Ц'],
            'Ya' => ['Я'],
            'Yu' => ['Ю'],
            'Zh' => ['Ж'],
            ' ' => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80", "\xEF\xBE\xA0"],
        ];
    }

    /**
     * Returns the language specific replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/3.1.0/LICENSE.txt
     *
     * @param string $language
     *
     * @return array|null
     */
    private static function languageSpecificCharsArray($language)
    {
        static $languageSpecific;

        if (! isset($languageSpecific)) {
            $languageSpecific = [
                'bg' => [
                    ['х', 'Х', 'щ', 'Щ', 'ъ', 'Ъ', 'ь', 'Ь'],
                    ['h', 'H', 'sht', 'SHT', 'a', 'А', 'y', 'Y'],
                ],
                'de' => [
                    ['ä',  'ö',  'ü',  'Ä',  'Ö',  'Ü'],
                    ['ae', 'oe', 'ue', 'AE', 'OE', 'UE'],
                ],
            ];
        }

        return $languageSpecific[$language] ?? null;
    }
}
