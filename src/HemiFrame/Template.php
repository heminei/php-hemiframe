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
     * @param array|\Traversable   $array    Result Array
     * @param object|null          $instance Object
     * @param string|\Closure|null $method   Method
     */
    public function setLoop(string $id, $array, $instance = null, $method = null): self
    {
        if (null == $method) {
            $method = $id;
        }

        if (!is_array($array) && (($array instanceof \Traversable) == false)) {
            throw new \InvalidArgumentException('Invalid array parameter');
        }

        do {
            $tag = 'wLoop';
            $element = '<'.$tag.' id="'.$id.'">';
            $outerHtml = $this->getElementOuterHtml($this->html, $element, $tag);
            $innerHtml = $this->getElementInnerHtml($this->html, $element, $tag);

            $htmlString = '';
            $i = 1;
            $iteration = new Lib\Loop\Iteration($i);
            if (!empty($array)) {
                $iteration->setTotalCount(count($array));
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
            }

            $this->html = $this->strReplaceFirst($outerHtml, $htmlString, $this->html);
        } while (strstr($this->html, $element));

        return $this;
    }

    public function setSwitcher(string $id, string $value): self
    {
        do {
            $tag = 'wSwitcher';
            $element = '<'.$tag.' id="'.$id.'">';
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
        $results = [];
        preg_match_all('/\<wSwitcher id\=\"(?<ids>.*)\">/', $this->html, $results);
        foreach ($results['ids'] as $id) {
            $tag = 'wSwitcher';
            $element = '<'.$tag.' id="'.$id.'">';
            $outerHtml = $this->getElementOuterHtml($this->html, $element, $tag);
            $this->html = $this->strReplaceFirst($outerHtml, '', $this->html);
        }

        return $this;
    }

    public function autoRemoveLoops($removeLoops = null)
    {
        if (null !== $removeLoops) {
            $this->autoRemoveLoops = $removeLoops;

            return $this;
        } else {
            return $this->autoRemoveLoops;
        }
    }

    public function autoRemoveSwitchers($removeSwitchers = null)
    {
        if (null !== $removeSwitchers) {
            $this->autoRemoveSwitchers = $removeSwitchers;

            return $this;
        } else {
            return $this->autoRemoveSwitchers;
        }
    }

    public function autoRemoveVariables($removeVariables = null)
    {
        if (null !== $removeVariables) {
            $this->autoRemoveVariables = $removeVariables;

            return $this;
        } else {
            return $this->autoRemoveVariables;
        }
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
}
