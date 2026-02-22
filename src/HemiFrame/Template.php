<?php

namespace HemiFrame;

/**
 * @author heminei <heminei@heminei.com>
 */
class Template
{
    private $html;
    private $vars = [];
    private $autoRemoveLoops = true;
    private $autoRemoveSwitchers = true;
    private $autoRemoveVariables = true;

    public function __construct(string $template)
    {
        if (mb_strlen($template) <= 260 && file_exists($template)) {
            if (is_readable($template)) {
                $this->html = file_get_contents($template);
            } else {
                throw new \InvalidArgumentException('The file is not readable.');
            }
        } else {
            $this->html = $template;
        }
    }

    public function __set(string $name, $value)
    {
        $this->setVar($name, $value);
    }

    public function __get(string $name)
    {
        return $this->getVar($name);
    }

    public function __toString(): string
    {
        return $this->parse();
    }

    public function setVar(string $name, $value): self
    {
        $this->vars[$name] = $value;

        return $this;
    }

    public function getVar(string $name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }

        return null;
    }

    public function replaceString(string $search, string $replace): self
    {
        $this->html = str_replace($search, $replace, $this->html);

        return $this;
    }

    /**
     * @param string               $id       Loop ID
     * @param iterable             $array    Result Array
     * @param object|null          $instance Object
     * @param string|\Closure|null $method   Method
     */
    public function setLoop(string $id, iterable $array, $instance = null, $method = null): self
    {
        if (null == $method) {
            $method = $id;
        }

        do {
            $tag = 'wLoop';
            $element = '<'.$tag.' id="'.$id.'">';
            $outerHtml = $this->getElementOuterHtml($this->html, $element, $tag);
            $innerHtml = $this->getElementInnerHtml($this->html, $element, $tag);

            $htmlString = '';
            $i = 1;
            $iteration = new Lib\Loop\Iteration($i);
            if (is_countable($array)) {
                $iteration->setTotalCount(count($array));
            }

            foreach ($array as $value) {
                $itemClass = new Template($innerHtml);
                $iteration->setIndex($i);
                if (null === $instance) {
                    $method($itemClass, $value, $iteration);
                } else {
                    $instance->$method($itemClass, $value, $iteration);
                }
                $htmlString .= $itemClass->parse();
                ++$i;
            }

            $this->html = $this->strReplaceFirst($outerHtml, $htmlString, $this->html);
        } while (strstr($this->html, $element));

        return $this;
    }

    public function setSwitcher(string $id, string $value): self
    {
        $this->applySwitcherValue($id, $value);

        return $this;
    }

    /**
     * Clear unused loops.
     */
    public function clearLoops(): self
    {
        $results = [];
        preg_match_all('/\<wLoop id\=\"(?<ids>.*)\">/', $this->html, $results);
        foreach ($results['ids'] as $id) {
            $tag = 'wLoop';
            $element = '<'.$tag.' id="'.$id.'">';
            $outerHtml = $this->getElementOuterHtml($this->html, $element, $tag);
            $this->html = $this->strReplaceFirst($outerHtml, '', $this->html);
        }

        return $this;
    }

    /**
     * Clear unused switches.
     */
    public function clearSwitchers(): self
    {
        foreach ($this->findAllElements('wSwitcher') as $element) {
            $outerHtml = $this->getElementOuterHtml($this->html, $element, 'wSwitcher');
            $this->html = $this->strReplaceFirst($outerHtml, '', $this->html);
        }

        return $this;
    }

    public function autoRemoveLoops($removeLoops = null)
    {
        if (null !== $removeLoops) {
            $this->autoRemoveLoops = $removeLoops;

            return $this;
        }

        return $this->autoRemoveLoops;
    }

    public function autoRemoveSwitchers($removeSwitchers = null)
    {
        if (null !== $removeSwitchers) {
            $this->autoRemoveSwitchers = $removeSwitchers;

            return $this;
        }

        return $this->autoRemoveSwitchers;
    }

    public function autoRemoveVariables($removeVariables = null)
    {
        if (null !== $removeVariables) {
            $this->autoRemoveVariables = $removeVariables;

            return $this;
        }

        return $this->autoRemoveVariables;
    }

    public function parse(): string
    {
        foreach ($this->vars as $key => $value) {
            if (is_array($value) or is_object($value)) {
                foreach ($value as $key1 => $value1) {
                    if (null === $value1) {
                        $value1 = '';
                    }
                    $tagToReplace = '{{'.$key.'.'.$key1.'}}';
                    $this->html = str_replace($tagToReplace, $value1, $this->html);
                }
            } else {
                if (null === $value) {
                    $value = '';
                }
                $tagToReplace = '{{'.$key.'}}';
                $this->html = str_replace($tagToReplace, $value, $this->html);
            }
        }

        foreach ($this->findAllElements('wSwitcher') as $element) {
            $attributes = $this->parseElementAttributes($element);
            if (!isset($attributes['id'], $attributes['default'])) {
                continue;
            }

            $this->applySwitcherValue($attributes['id'], $attributes['default']);
        }

        if ($this->autoRemoveLoops()) {
            $this->clearLoops();
        }
        if ($this->autoRemoveSwitchers()) {
            $this->clearSwitchers();
        }
        if ($this->autoRemoveVariables()) {
            $this->html = preg_replace('/{{(.*?)}}/s', '', $this->html);
        }

        return $this->html;
    }

    public function view(): void
    {
        echo $this->parse();
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    private function applySwitcherValue(string $id, string $value): void
    {
        do {
            $tag = 'wSwitcher';
            $element = $this->findElementById($this->html, $tag, $id);

            if (empty($element)) {
                throw new \InvalidArgumentException("Can't find switcher with ID:".$id);
            }

            $outerHtml = $this->getElementOuterHtml($this->html, $element, $tag);
            $innerHtml = $this->getElementInnerHtml($this->html, $element, $tag);

            if (empty($outerHtml)) {
                throw new \InvalidArgumentException("Can't find switcher with ID:".$id);
            }

            $tagCase = 'case';
            $elementCase = '<'.$tagCase.' value="'.$value.'">';
            $innerHtmlCase = $this->getElementInnerHtml($innerHtml, $elementCase, $tagCase);

            if (empty($innerHtmlCase)) {
                throw new \InvalidArgumentException("Can't find case with value '".$value."' in switcher with ID '".$id."'");
            }

            $this->html = $this->strReplaceFirst($outerHtml, $innerHtmlCase, $this->html);
        } while (strstr($this->html, $element));
    }

    private function strReplaceFirst(?string $search, string $replace, string $subject): string
    {
        if (null === $search) {
            return $subject;
        }
        $pos = strpos($subject, $search);
        if (false !== $pos) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    private function getElementOuterHtml(string $html, string $element, string $tag): ?string
    {
        $innerHtml = $this->getElementInnerHtml($html, $element, $tag);
        if (null == $innerHtml) {
            return null;
        }

        $outerHtml = $element.$innerHtml.'</'.$tag.'>';

        return $outerHtml;
    }

    private function getElementInnerHtml(string $html, string $element, string $tag): ?string
    {
        $startArray = explode($element, $html, 2);
        if (!isset($startArray[1])) {
            return null;
        }
        $endArray = explode('</'.$tag.'>', $startArray[1]);
        $countOpenTags = 0;
        $innerHtml = '';
        foreach ($endArray as $key => $value) {
            $countOpenTags = $countOpenTags + substr_count($value, '<'.$tag.' ');
            $innerHtml .= $value;
            if ($key == $countOpenTags) {
                break;
            }
            $innerHtml .= '</'.$tag.'>';
        }

        return $innerHtml;
    }

    private function findElementById(string $html, string $tag, string $id): ?string
    {
        foreach ($this->findAllElements($tag, $html) as $element) {
            $attributes = $this->parseElementAttributes($element);
            if (($attributes['id'] ?? null) === $id) {
                return $element;
            }
        }

        return null;
    }

    private function findAllElements(string $tag, ?string $html = null): array
    {
        if (null === $html) {
            $html = $this->html;
        }

        $matches = [];
        preg_match_all('/<'.preg_quote($tag, '/').'\s+[^>]*>/', $html, $matches);

        return $matches[0];
    }

    private function parseElementAttributes(string $element): array
    {
        $attributes = [];
        preg_match_all('/(?<name>[a-zA-Z_:][a-zA-Z0-9_:-]*)\s*=\s*"(?<value>[^"]*)"/', $element, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attributes[$match['name']] = $match['value'];
        }

        return $attributes;
    }
}
