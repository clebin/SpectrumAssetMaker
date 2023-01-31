<?php

namespace ClebinGames\SpectrumAssetMaker;

define('COLRED', "");
define('COLGREEN', "");
define('COLERROR', "");
define('COLCYAN', "");
define('COLYELLOW', "");
define('COLEND', "");
define('COLMAGENTA', "");

class CliTools
{
    /**
     * Ask the user a question
     */
    public static function GetAnswer($question, $default = false, $other_options = [], $strict_values = true)
    {
        // build the question
        $str_question = COLCYAN . $question . COLEND;

        if ($default !== false) {
            $str_question .= ' [' . COLGREEN;

            if (sizeof($other_options) > 0) {
                $str_question .= strtoupper($default);
            } else {
                $str_question .= $default;
            }

            $str_question .= COLEND;

            foreach ($other_options as $option) {
                $str_question .= '/' . $option;
            }

            $str_question .= ']';
        }

        $str_question .= COLCYAN . ':' . COLEND . ' ';

        $answer = false;

        while ($answer === false) {
            $answer = self::GetAnswerPrompt($str_question, $default, $other_options, $strict_values);

            if ($answer === false) {
                echo 'Error: Value not allowed.';
            }
        }

        return $answer;
    }

    /**
     * Prompt and read input
     */
    private static function GetAnswerPrompt($question, $default = false, $other_options = [], $strict_values = true)
    {
        // prompt the user
        $answer = readline($question);

        // set default
        if ($answer == '' && $default !== false) {
            return $default;
        }
        // not allowed
        elseif ($strict_values === true && sizeof($other_options) > 0 && !in_array($answer, $other_options)) {
            return false;
        }

        return $answer;
    }

    /**
     * Get answer to a true/false question
     */
    public static function GetAnswerBoolean($question, $default = true)
    {
        if ($default === true) {
            $str_default = 'y';
            $other_options = ['n'];
        } else {
            $str_default = 'n';
            $other_options = ['y'];
        }

        $answer = self::GetAnswer($question, $str_default, $other_options, false);

        if (strtolower($answer) === 'y' || strtolower($answer) === 'yes' || strtolower($answer) === 'true') {
            return true;
        } elseif (strtolower($answer) === 'n' || strtolower($answer) === 'no' || strtolower($answer) === 'false') {
            return false;
        } else {
            return $default;
        }
    }
}
