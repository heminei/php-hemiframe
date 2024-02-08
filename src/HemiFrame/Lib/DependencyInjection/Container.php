<?php

namespace HemiFrame\Lib\DependencyInjection;

use HemiFrame\Lib\DependencyInjection\Attributes\Singleton;

/**
 * @author heminei <heminei@heminei.com>
 */
class Container implements \HemiFrame\Interfaces\DependencyInjection\Container
{
    private $rules = [];
    private $instances = [];

    /**
     * @return $this
     *
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

    public function getRule(string $name): array
    {
        if (isset($this->rules[$name])) {
            return $this->rules[$name];
        }

        return [];
    }

    /**
     * @template T
     *
     * @param class-string<T> $name
     *
     * @return T
     *
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
        } elseif (isset($rule['instanceOf'])) {
            $className = $rule['instanceOf'];
        } else {
            $className = $name;
        }
        if (empty($class)) {
            if (!class_exists($className)) {
                throw new Exception("$className - class not found");
            }

            $reflection = new \ReflectionClass($className);

            $docComment = $reflection->getDocComment();
            if (strstr($docComment, '@Singleton')) {
                $rule['singleton'] = true;
            }
            if (!empty($reflection->getAttributes(Singleton::class))) {
                $rule['singleton'] = true;
            }

            $parentClass = $reflection->getParentClass();
            if (!empty($parentClass)) {
                $parentDocComment = $parentClass->getDocComment();
                if (strstr($parentDocComment, '@Singleton')) {
                    $rule['singleton'] = true;
                }
                if (!empty($parentClass->getAttributes(Singleton::class))) {
                    $rule['singleton'] = true;
                }
            }

            $constructor = $reflection->getConstructor();
            if (!empty($constructor) && empty($arguments)) {
                $constructorParams = $reflection->getConstructor()->getParameters();
                foreach ($constructorParams as $param) {
                    /** @var \ReflectionNamedType|null $type */
                    $type = $param->getType();
                    if (!empty($type) && $type->isBuiltin()) {
                        continue;
                    }
                    $type = (string) $param->getType();
                    if (!empty($type)) {
                        $arguments[] = $this->get($type);
                    } elseif ($param->isOptional()) {
                        $arguments[] = $param->getDefaultValue();
                    } else {
                        // throw new Exception("Invalid constructor injection in " . $reflection->getName());
                    }
                }
            }
            $class = $reflection->newInstanceWithoutConstructor();
            $this->injectProperties($class, $reflection);
            if (!empty($constructor)) {
                call_user_func_array([$class, '__construct'], $arguments);
            }
        }

        if (isset($rule['call'])) {
            if (!is_array($rule['call'])) {
                throw new Exception("Rule 'call' must be a array");
            }
            foreach ($rule['call'] as $key => $value) {
                call_user_func_array([$class, $key], $value);
            }
        }

        if (isset($rule['singleton']) && true == $rule['singleton']) {
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
            if (!strstr($docComment, '@Inject')) {
                continue;
            }
            $injectName = null;
            $lines = explode("\n", $docComment);
            foreach ($lines as $line) {
                if (strstr($line, '@Inject')) {
                    $stringArray = explode('@Inject ', $line);
                    if (isset($stringArray[1])) {
                        $injectName = trim($stringArray[1]);
                        break;
                    }
                }
                if (strstr($line, '@var')) {
                    $stringArray = explode('@var ', $line);
                    if (isset($stringArray[1])) {
                        $injectName = trim($stringArray[1]);
                        break;
                    }
                }
            }
            if (empty($injectName)) {
                throw new Exception('Invalid property injection in '.$reflection->getName().', property name: '.$property->getName());
            }
            if (0 === strpos($injectName, '\\')) {
                $injectName = substr($injectName, 1);
            } else {
                $injectName = $reflection->getNamespaceName().'\\'.$injectName;
            }

            $property->setAccessible(true);
            $property->setValue($class, $this->get($injectName));
        }
    }
}
