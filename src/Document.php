#!/usr/bin/env php
<?php

declare(strict_types = 1);

/**
 * Jason
 * A convenient wrapper for JSON documents
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @license MIT
 */

namespace Jason;

use InvalidArgumentException;

class Document {

    /**
     * Items array
     * @var array
     */
    protected array $items;

    /**
     * Constructor
     * @param array|string $contents Document contents
     */
    public function __construct(string|array $contents = '') {
        if ( is_array($contents) ) {
            $this->items = $contents;
        } else {
            $this->items = $contents ? self::decode($contents) : [];
        }
    }

    /**
     * Create a Document from an array
     * @param  array  $items Document contents
     */
    public static function fromArray(array $items): self {
        $document = new Document($items);
        return $document;
    }

    /**
     * Create a Document from an string
     * @param  string $contents Document contents
     */
    public static function fromString(string $contents): self {
        $document = new Document($contents);
        return $document;
    }

    /**
     * Create a Document from a file contents
     * @param  string $path File path, must be readable
     */
    public static function fromFile(string $path): self {
        if (! file_exists($path) ) throw new InvalidArgumentException('The specified file does not exist');
        if (! is_file($path) ) throw new InvalidArgumentException('The specified path is not valid');
        if (! is_readable($path) ) throw new InvalidArgumentException('The specified file is not readable');
        $contents = file_get_contents($path);
        $document = new Document($contents ?: '');
        return $document;
    }

    /**
     * Create a Document from a stream contents
     * @param  mixed  $stream Stream handler, must be readable
     */
    public static function fromStream(mixed $stream): self {
        if (! is_resource($stream) ) throw new InvalidArgumentException('Invalid stream handle');
        $meta = stream_get_meta_data($stream);
        if (! is_readable( $meta['uri'] ) ) throw new InvalidArgumentException('The specified stream is not readable');
        $contents = stream_get_contents($stream);
        $document = new Document($contents ?: '');
        return $document;
    }

    /**
     * Decode a JSON string
     * @param  string $json JSON string
     */
    public static function decode(string $json): array {
        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Encode a value to JSON
     * @param  mixed $value  Value
     * @param  bool  $pretty Pretty print flag
     */
    public static function encode(mixed $value, bool $pretty = false): string {
        return json_encode($value, ($pretty ? JSON_PRETTY_PRINT : 0) | JSON_THROW_ON_ERROR);
    }

    /**
     * Get an string representation of the Document
     * @param  bool $pretty Pretty print flag
     */
    public function toString(bool $pretty = false): string {
        return self::encode($this->items, $pretty);
    }

    /**
     * Save the Document to a file
     * @param  string $path      File path, must be writeable
     * @param  bool   $overwrite Overwrite flag
     * @param  bool   $pretty    Pretty print flag
     */
    public function toFile(string $path, bool $overwrite = false, bool $pretty = false): bool {
        if ( is_dir($path) ) throw new InvalidArgumentException('The specified path is not valid');
        if ( !$overwrite && !file_exists($path) ) throw new InvalidArgumentException('The specified file already exists');
        if ( $overwrite && !is_writeable($path) ) throw new InvalidArgumentException('The specified file is not writeable');
        file_put_contents($path, $this->toString($pretty));
        return true;
    }

    /**
     * Save the Document to a stream
     * @param  mixed $stream Stream handle, must be writeable
     * @param  bool  $pretty Pretty print flag
     */
    public function toStream(mixed $stream, bool $pretty = false): bool {
        if (! is_resource($stream) ) throw new InvalidArgumentException('Invalid stream handle');
        $meta = stream_get_meta_data($stream);
        if (! is_writeable( $meta['uri'] ) ) throw new InvalidArgumentException('The specified stream is not writeable');
        fwrite($stream, $this->toString($pretty));
        return true;
    }

    /**
     * Get an array representation of the Document
     */
    public function toArray(): array {
        return $this->items;
    }

    /**
     * Check if a given key or keys exists
     * @param  array|int|string  $keys
     */
    public function has(array|int|string $keys): bool {
        $keys = (array) $keys;
        if (!$this->items || $keys === []) {
            return false;
        }
        foreach ($keys as $key) {
            $items = $this->items;
            if ($this->exists($items, $key)) {
                continue;
            }
            foreach (explode('.', $key) as $segment) {
                if (!is_array($items) || !$this->exists($items, $segment)) {
                    return false;
                }
                $items = $items[$segment];
            }
        }
        return true;
    }

    /**
     * Return the value of a given key
     * @param  int|string  $key
     * @param  mixed       $default
     */
    public function get(int|string $key, mixed $default = null): mixed {
        if ($this->exists($this->items, $key)) {
            return $this->items[$key];
        }
        if (!is_string($key) || strpos($key, '.') === false) {
            return $default;
        }
        $items = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($items) || !$this->exists($items, $segment)) {
                return $default;
            }
            $items = &$items[$segment];
        }
        return $items;
    }

    /**
     * Set a given key / value pair or pairs
     * @param  array|int|string  $keys
     * @param  mixed             $value
     */
    public function set(array|int|string $keys, mixed $value = null): self {
        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $this->set($key, $value);
            }
            return $this;
        }
        $items = &$this->items;
        if (is_string($keys)) {
            foreach (explode('.', $keys) as $key) {
                if (!isset($items[$key]) || !is_array($items[$key])) {
                    $items[$key] = [];
                }
                $items = &$items[$key];
            }
        }
        $items = $value;
        return $this;
    }

    /**
     * Checks if the given key exists in the provided array.
     * @param  array       $array Array to validate
     * @param  int|string  $key   The key to look for
     */
    protected function exists(array $array, int|string $key): bool {
        return array_key_exists($key, $array);
    }
}
