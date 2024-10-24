<?php

declare(strict_types=1);

/**
 * Post Limit mod (SMF)
 *
 * @package PostLimit
 * @version 1.1
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2024  Michel Mendiola
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace PostLimit;

class PostLimitUtils
{
    public function sanitize($variable)
    {
        global $smcFunc;

        if (is_array($variable)) {
            foreach ($variable as $key => $variableValue) {
                $variable[$key] = $this->sanitize($variableValue);
            }

            return array_filter($variable);
        }

        $var = $smcFunc['htmlspecialchars'](
            $smcFunc['htmltrim']((string) $variable),
            \ENT_QUOTES
        );

        if (ctype_digit($var)) {
            $var = (int) $var;
        }

        return $var;
    }

    public function text(string $textKey = ''): string
    {
        global $txt;

        $fullKey = PostLimit::NAME . '_' . $textKey;

        if (empty($txt[$fullKey])) {
            loadLanguage(PostLimit::NAME);
        }

        return $txt[$fullKey];
    }

    public function setting(string $settingKey = '', $defaultValue = false)
    {
        global $modSettings;

        return $modSettings[$settingKey] ?? $defaultValue;
    }

    public function setContext(array $values): void
    {
        global $context;

        foreach ($values as $key => $value) {
            $context[$key] = $value;
        }
    }
}
