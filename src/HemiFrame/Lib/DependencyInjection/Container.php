<?php

namespace HemiFrame\Lib\DependencyInjection;

/**
 * @author heminei <heminei@heminei.com>
 */
class Container implements \HemiFrame\Interfaces\DependencyInjection\Container
{

    private $rules = [];
    private $instances = [];

    /**
     *
     * @param string $name
     * @param array $rules
     * @return $this
     * @throws Exception
     */
    public function setRule(string $name, array $rules)
    {
        if (isset($this->rules[$name])) {
            throw new Exception("$name - rule name exist");
        }
        $this->rules[$name] = $rules;

        return $this;
    }

    /**
     *
     * @param string $name
     * @return array
     */
    public function getRule(string $name): array
    {
        if (isset($this->rules[$name])) {
            return $this->rules[$name];
        }
        return [];
    }

    /**
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function get(string $name, array $arguments = [])
    {
        $rule = [];
        $class = null;

        //        var_dump($name);
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        if (isset($this->rules[$name])) {
            $rule = $this->rules[$name];
        }
        if (isset($rule['instance'])) {
            if (!is_callable($rule['instance'])) {
                throw new Exception("Rule 'instance' must be a function");
            }
            $class = $rule['instance']($name, $arguments);
            $className = null;
        } else if (isset($rule['instanceOf'])) {
            $className = $rule['instanceOf'];
        } else {
            $className = $name;
        }
        if (empty($class)) {
            if (!class_exists($className)) {
                throw new Exception("$className - class not found");
            }

            $reflection = new \ReflectionClass($className);
            $constructor = $reflection->getConstructor();
            if (!empty($constructor) && empty($arguments)) {
                $constructorParams = $reflection->getConstructor()->getParameters();
                foreach ($constructorParams as $param) {
                    /** @var \ReflectionParameter $param */
                    if (!empty($param->getClass())) {
                        $arguments[] = $this->get($param->getClass()->getName());
                    } elseif ($param->isOptional()) {
                        $arguments[] = $param->getDefaultValue();
                    } else {
                        //throw new Exception("Invalid constructor injection in " . $reflection->getName());
                    }
                }
            }
            $class = $reflection->newInstanceWithoutConstructor();
            if (!empty($constructor)) {
                call_user_func_array(array($class, '__construct'), $arguments);
            }

            $this->injectProperties($class, $reflection);
        }

        if (isset($rule['call'])) {
            if (!is_array($rule['call'])) {
                throw new Exception("Rule 'call' must be a array");
            }
            foreach ($rule['call'] as $key => $value) {
                call_user_func_array(array($class, $key), $value);
            }
        }

        if (isset($rule['singleton']) && $rule['singleton'] == true) {
            $this->instances[$name] = $class;
        }

        return $class;
    }

    private function injectProperties($class, \ReflectionClass $reflection)
    {
        if ($reflection->getParentClass()) {
            $this->injectProperties($class, $reflection->getParentClass());
        }
        //        var_dump($reflection->getName());
        foreach ($reflection->getProperties() as $property) {
            /** @var \ReflectionProperty $property */
            $docComment = $property->getDocComment();
            //            var_dump($property->getName());
            if (!strstr($docComment, "@Inject")) {
                continue;
            }
            $injectName = null;
            $lines = explode("\n", $docComment);
            foreach ($lines as $line) {
                if (strstr($line, "@Inject")) {
                    $stringArray = explode("@Inject ", $line);
                    if (isset($stringArray[1])) {
                        $injectName = trim($stringArray[1]);
                        break;
                    }
                }
                if (strstr($line, "@var")) {
                    $stringArray = explode("@var ", $line);
                    if (isset($stringArray[1])) {
                        $injectName = trim($stringArray[1]);
                        break;
                    }
                }
            }
            if (empty($injectName)) {
                throw new Exception("Invalid property injection in " . $reflection->getName() . ", property name: " . $property->getName());
            }
            if (strpos($injectName, "\\") === 0) {
                $injectName = substr($injectName, 1);
            } else {
                $injectName = $reflection->getNamespaceName() . "\\" . $injectName;
            }

            $property->setAccessible(true);
            $property->setValue($class, $this->get($injectName));
        }
    }
}
