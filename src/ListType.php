<?php

namespace eru123\NoEngine;

use \Iterator;

class ListType implements Iterator
{
    private $index = 0;
    private $position = null;
    private $array = [];

    public function __construct($arr = [])
    {
        $this->rewind();
        $this->array = $arr;
    }

    public function rewind(): void
    {
        if (is_array($this->array) && count($this->array) > 0) {
            $this->position = array_keys($this->array)[0];
        } else $this->position = null;
    }

    public function current()
    {
        return $this->array[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->index;
        if ($this->index < count($this->array)) {
            $this->position = array_keys($this->array)[$this->index];
        } else {
            $this->position = null;
        }
    }

    public function keys(): array
    {
        return array_keys($this->array);
    }

    public function values(): array
    {
        return array_values($this->array);
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->array[$this->position]);
    }

    public function implode($separator = ',')
    {
        return implode($separator, $this->array);
    }

    public function to_string()
    {
        return "[{$this->implode(', ')}]";
    }

    public function size(): int
    {
        return count($this->array);
    }

    public function first()
    {
        foreach ($this->array as $key => $item) return ['key' => $key, 'value' => $item];
    }

    public function last()
    {
        $reverse_keys = array_reverse(array_keys($this->array));
        foreach ($reverse_keys as $key) return ['key' => $key, 'value' => $this->array[$key]];
    }

    public function map($callback)
    {
        $new_array = [];
        foreach ($this->array as $key => $item) $new_array[$key] = $callback($key, $item);
        return new self($new_array);
    }

    public function filter($callback)
    {
        $new_array = [];
        foreach ($this->array as $key => $item) if ($callback($item)) $new_array[$key] = $item;
        return new self($new_array);
    }

    public function reduce($callback, $initial = NULL)
    {
        $result = $initial;
        foreach ($this->array as $item) $result = $callback($result, $item);
        return $result;
    }

    public function reverse()
    {
        $new_array = [];
        $reverse_keys = array_reverse(array_keys($this->array));
        foreach ($reverse_keys as $key) $new_array[$key] = $this->array[$key];
        return new self($new_array);
    }

    public function sort($callback)
    {
        $new_array = [];
        $keys = array_keys($this->array);
        usort($keys, $callback);
        foreach ($keys as $key) $new_array[$key] = $this->array[$key];
        return new self($new_array);
    }

    public function slice($offset, $length = NULL)
    {
        $new_array = [];
        $keys = array_keys($this->array);
        $keys = array_slice($keys, $offset, $length);
        foreach ($keys as $key) $new_array[$key] = $this->array[$key];
        return new self($new_array);
    }

    public function splice($offset, $length = NULL, $replacement = NULL)
    {
        $new_array = [];
        $keys = array_keys($this->array);
        $keys = array_splice($keys, $offset, $length, $replacement);
        foreach ($keys as $key) $new_array[$key] = $this->array[$key];
        return new self($new_array);
    }

    public function pop()
    {
        $keys = array_keys($this->array);
        $key = array_pop($keys);
        $value = $this->array[$key];
        unset($this->array[$key]);
        return ['key' => $key, 'value' => $value];
    }

    public function shift()
    {
        $keys = array_keys($this->array);
        $key = array_shift($keys);
        $value = $this->array[$key];
        unset($this->array[$key]);
        return ['key' => $key, 'value' => $value];
    }

    public function push(...$args)
    {
        if (count($args) == 2 && is_string($args[0])) {
            $this->array[$args[0]] = $args[1];
        } else {
            foreach ($args as $item) $this->array[] = $item;
        }
        return $this;
    }

    public function unshift(...$args)
    {
        if (count($args) == 2 && is_string($args[0])) {
            $this->array[$args[0]] = $args[1];
        } else {
            foreach ($args as $item) $this->array[] = $item;
        }
        return $this;
    }

    public function is_array()
    {
        return array_keys($this->array) === range(0, count($this->array) - 1);
    }

    public function is_object()
    {
        return !$this->is_array();
    }

    public function mask($mask = "?")
    {
        $masks = [];
        foreach ($this as $key => $v) {
            $masks[$key] = $mask;
        }
        return new self($masks);
    }

    public function toArray()
    {
        return $this->array;
    }

    public function get($key, $default = NULL)
    {
        return isset($this->array[$key]) ? $this->array[$key] : $default;
    }

    public function has($value)
    {
        foreach ($this->array as $v) {
            if ($v == $value) return true;
        }
        return false;
    }

    public function hasStrict($value)
    {
        foreach ($this->array as $v) {
            if ($v === $value) return true;
        }
        return false;
    }

    public function hasKey($key)
    {
        return isset($this->array[$key]);
    }

    public function delete($key)
    {
        unset($this->array[$key]);
        return $this;
    }

    public function remove($value)
    {
        foreach ($this->array as $key => $v) {
            if ($v == $value) unset($this->array[$key]);
        }
        return $this;
    }
}
