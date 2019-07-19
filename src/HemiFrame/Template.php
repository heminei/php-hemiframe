<?php

namespace HemiFrame;

/**
 * @author heminei <heminei@heminei.com>
 */
class Template
{
    private $html = null;
    private $templateVars = array();
    private $tagMarker = null;
    private $tag;
    private $autoRemoveLoops = true;
    private $autoRemoveSwitchers = true;
    private $autoRemoveVariables = true;

    public function __construct(string $template)
    {
        $path = $template;
        if (mb_strlen($path) <= 260 && file_exists($path)) {
            if (is_readable($path)) {
                $this->html = file_get_contents($path);
            } else {
                throw new \Exception("File can not be read.");
            }
        } else {
            $this->html = $template;
        }
    }

    public function __set(string $name, $value)
    {
        $this->templateVars[$name] = $value;
    }

    public function __get(string $name)
    {
        return $this->getVar($name);
    }

    public function __toString(): string
    {
        return $this->parse();
    }

    public function getVar(string $name)
    {
        if (isset($this->templateVars[$name])) {
            return $this->templateVars[$name];
        } else {
            return null;
        }
    }

    public function replaceString(string $search, string $replace): self
    {
        $this->html = str_replace($search, $replace, $this->html);

        return $this;
    }

    public function innerHtml($html = null)
    {
        if ($this->tagMarker !== null) {
            preg_match("/" . $this->tagMarker . "(.*?)>/s", $this->html, $matchesStartTag);
            $startTag = $matchesStartTag[0];
            $endTag = "</" . $this->tag . ">";

            $htmlArray = explode($startTag, $this->html);
            $openTagCount = substr_count($htmlArray[1], "<" . $this->tag);
            $closeTagCount = substr_count($htmlArray[1], $endTag);
            if ($openTagCount + 1 != $closeTagCount) {
                throw new \Exception("Not closed tag: " . $this->tag . $this->html);
            }

            $check = explode($endTag, $htmlArray[1]);
            $j = 0;
            for ($i = 0; $i < count($check); $i++) {
                $c = count(explode("<" . $this->tag, $check[$i])) - 1;
                if (strstr($check[$i], "<" . $this->tag)) {
                    $j = $j + $c;
                }
                if ($j - $i <= 0) {
                    break;
                }
            }
            $innerHtml = explode($endTag, $htmlArray[1]);
            $innerHtmlSlice = array_slice($innerHtml, 0, $j + 1);
            $innerHtml = implode($endTag, $innerHtmlSlice);

            if ($html === null) {
                return $innerHtml;
            } else {
                $this->replaceString($innerHtml, $html);
            }
        } else {
            throw new \Exception("Tag marker not selected");
        }

        return $this;
    }

    public function outernHtml($html = null): self
    {
        if ($this->tagMarker !== null) {
            preg_match("/" . $this->tagMarker . "(.*?)>/s", $this->html, $matchesStartTag);
            $startTag = $matchesStartTag[0];
            $endTag = "</" . $this->tag . ">";

            $innerHtml = $this->innerHtml();

            $outernHtml = $startTag . $innerHtml . $endTag;
            if ($html === null) {
                return $outernHtml;
            } else {
                $this->replaceString($outernHtml, $html);
            }
        } else {
            throw new \Exception("Tag marker not selected");
        }

        return $this;
    }

    private function setTagMarker($html, $marker): self
    {
        if (strstr($this->html, $html)) {
            $this->html = $this->strReplaceFirst($html, $marker, $this->html);
        } else {
            throw new \Exception("$html not found");
        }

        return $this;
    }

    /**
     *
     * @param string $id Loop ID;
     * @param array $array Result Array
     * @param object $instance Object
     * @param string $method Method
     */
    public function setLoop(string $id, array $array, $instance, string $method = null): self
    {
        $this->clearTagMarker();
        if ($method == null) {
            $method = $id;
        }

        do {
            $this->tag = "wLoop";
            $this->tagMarker = "<:selectParser:" . $this->tag;
            $this->setTagMarker("<" . $this->tag . " id=\"$id\"", $this->tagMarker . " id=\"$id\"");

            $loopHtml = $this->innerHtml();

            $htmlString = "";
            $i = 1;
            $iteration = new \HemiFrame\Lib\Loop\Iteration($i);
            if (!empty($array)) {
                $iteration->setTotalCount(count($array));
                foreach ($array as $value) {
                    $itemClass = new Template($loopHtml);
                    $iteration->setIndex($i);
                    $instance->$method($itemClass, $value, $iteration);
                    $htmlString .= $itemClass->parse();
                    $i++;
                }
                $this->outernHtml($htmlString);
            } else {
                $this->outernHtml("");
            }
        } while (strstr($this->html, '<wLoop id="' . $id . '"'));

        return $this;
    }

    public function setSwitcher(string $id, string $value): self
    {
        $this->clearTagMarker();

        do {
            $htmlOr = $this->html;

            $this->tag = "wSwitcher";
            $this->tagMarker = "<:selectParser:" . $this->tag;
            $this->setTagMarker("<" . $this->tag . " id=\"$id\"", $this->tagMarker . " id=\"$id\"");

            $this->html = $this->innerHtml();

            $this->tag = "case";
            $this->tagMarker = "<:selectParser:" . $this->tag;
            $this->setTagMarker("<" . $this->tag . " value=\"$value\"", $this->tagMarker . " value=\"$value\"");

            $caseHtml = $this->innerHtml();

            $this->html = $htmlOr;

            $this->tag = "wSwitcher";
            $this->tagMarker = "<:selectParser:" . $this->tag;
            $this->setTagMarker("<" . $this->tag . " id=\"$id\"", $this->tagMarker . " id=\"$id\"");

            $this->outernHtml($caseHtml);
        } while (strstr($this->html, '<wSwitcher id="' . $id . '"'));

        return $this;
    }

    public function clearLoops(): self
    {
        if (strstr($this->html, "<wLoop")) {
            do {
                $this->tag = "wLoop";
                $this->tagMarker = "<:selectParser:" . $this->tag;
                $this->setTagMarker("<" . $this->tag . "", $this->tagMarker);
                $this->outernHtml(" ");
            } while (strstr($this->html, "<wLoop"));
        }

        return $this;
    }

    public function clearSwitchers(): self
    {
        if (strstr($this->html, "<wSwitcher")) {
            do {
                $this->tag = "wSwitcher";
                $this->tagMarker = "<:selectParser:" . $this->tag;
                $this->setTagMarker("<" . $this->tag . "", $this->tagMarker);
                $this->outernHtml(" ");
            } while (strstr($this->html, "<wSwitcher"));
        }

        return $this;
    }

    private function clearTagMarker(): self
    {
        $this->replaceString(":selectParser:", "");

        return $this;
    }

    public function autoRemoveLoops($removeLoops = null)
    {
        if ($removeLoops !== null) {
            $this->autoRemoveLoops = $removeLoops;
            return $this;
        } else {
            return $this->autoRemoveLoops;
        }
    }

    public function autoRemoveSwitchers($removeSwitchers = null)
    {
        if ($removeSwitchers !== null) {
            $this->autoRemoveSwitchers = $removeSwitchers;
            return $this;
        } else {
            return $this->autoRemoveSwitchers;
        }
    }

    public function autoRemoveVariables($removeVariables = null)
    {
        if ($removeVariables !== null) {
            $this->autoRemoveVariables = $removeVariables;
            return $this;
        } else {
            return $this->autoRemoveVariables;
        }
    }

    public function parse(): string
    {
        foreach ($this->templateVars as $key => $value) {
            if (is_array($value) or is_object($value)) {
                foreach ($value as $key1 => $value1) {
                    $tagToReplace = "{{" . $key . "." . $key1 . "}}";
                    $this->html = str_replace($tagToReplace, $value1, $this->html);
                }
            } else {
                $tagToReplace = "{{" . $key . "}}";
                $this->html = str_replace($tagToReplace, $value, $this->html);
            }
        }

        if ($this->autoRemoveLoops()) {
            $this->clearLoops();
        }
        if ($this->autoRemoveSwitchers()) {
            $this->clearSwitchers();
        }
        if ($this->autoRemoveVariables()) {
            $this->html = preg_replace('/{{(.*?)}}/s', "", $this->html);
        }

        return $this->html;
    }

    public function view()
    {
        echo $this->parse();
    }

    private function strReplaceFirst(string $search, string $replace, string $subject): string
    {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

}
