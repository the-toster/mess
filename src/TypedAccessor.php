<?php
declare(strict_types=1);

namespace Zakirullin\TypedAccessor;

use Zakirullin\TypedAccessor\Exception\CannotModifyAccessorException;
use Zakirullin\TypedAccessor\Exception\UncastableValueException;
use Zakirullin\TypedAccessor\Exception\UnexpectedKeyTypeException;
use Zakirullin\TypedAccessor\Exception\UnexpectedTypeException;
use function array_keys;
use function count;
use function filter_var;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function key_exists;
use function range;
use const FILTER_VALIDATE_INT;

/**
 * @psalm-immutable
 */
final class TypedAccessor implements TypedAccessorInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @psalm-allow-private-mutation
     * @var array
     */
    private $keySequence = [];

    /**
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @psalm-pure
     * @return int
     */
    public function getInt(): int
    {
        $intValue = $this->findInt();
        if ($intValue === null) {
            throw new UnexpectedTypeException('int', $this->value, $this->keySequence);
        }

        return $intValue;
    }

    /**
     * @psalm-pure
     * @return bool
     */
    public function getBool(): bool
    {
        $boolValue = $this->findBool();
        if ($boolValue === null) {
            throw new UnexpectedTypeException('bool', $this->value, $this->keySequence);
        }

        return $boolValue;
    }

    /**
     * @psalm-pure
     * @return string
     */
    public function getString(): string
    {
        $stringValue = $this->findString();
        if ($stringValue === null) {
            throw new UnexpectedTypeException('string', $this->value, $this->keySequence);
        }

        return $stringValue;
    }

    /**
     * @psalm-pure
     * @psalm-return list<int>
     * @return array
     */
    public function getListOfInt(): array
    {
        $listOfInt = $this->findListOfInt();
        if ($listOfInt === null) {
            throw new UncastableValueException('list_of_int', $this->value, $this->keySequence);
        }

        return $this->value;
    }

    /**
     * @psalm-pure
     * @psalm-return list<string>
     * @return array
     */
    public function getListOfString(): array
    {
        $listOfString = $this->findListOfString();
        if ($listOfString === null) {
            throw new UncastableValueException('list_of_string', $this->value, $this->keySequence);
        }

        return $this->value;
    }

    /**
     * @psalm-pure
     * @return int
     */
    public function getAsInt(): int
    {
        $intValue = $this->findAsInt();
        if ($intValue === null) {
            throw new UncastableValueException('int', $this->value, $this->keySequence);
        }

        return $intValue;
    }

    /**
     * @psalm-pure
     * @return bool
     */
    public function getAsBool(): bool
    {
        $boolValue = $this->findAsBool();
        if ($boolValue === null) {
            throw new UncastableValueException('bool', $this->value, $this->keySequence);
        }

        return $boolValue;
    }

    /**
     * @psalm-pure
     * @return string
     */
    public function getAsString(): string
    {
        $stringValue = $this->findAsString();
        if ($stringValue === null) {
            throw new UncastableValueException('string', $this->value, $this->keySequence);
        }

        return $stringValue;
    }

    /**
     * @psalm-pure
     * @return array
     */
    public function getAsListOfInt(): array
    {
        $listOfInt = $this->findAsListOfInt();
        if ($listOfInt === null) {
            throw new UncastableValueException('list_of_int', $this->value, $this->keySequence);
        }

        return $listOfInt;
    }

    /**
     * @psalm-pure
     * @return array
     */
    public function getAsListOfString(): array
    {
        $listOfString = $this->findAsListOfString();
        if ($listOfString === null) {
            throw new UncastableValueException('list_of_string', $this->value, $this->keySequence);
        }

        return $listOfString;
    }

    /**
     * @psalm-pure
     * @return int|null
     */
    public function findInt(): ?int
    {
        if (!is_int($this->value)) {
            return null;
        }

        return $this->value;
    }

    /**
     * @psalm-pure
     * @return bool|null
     */
    public function findBool(): ?bool
    {
        if (!is_bool($this->value)) {
            return null;
        }

        return $this->value;
    }

    /**
     * @psalm-pure
     * @return string|null
     */
    public function findString(): ?string
    {
        if (!is_string($this->value)) {
            return null;
        }

        return $this->value;
    }

    /**
     * @psalm-pure
     * @return array|null
     */
    public function findListOfInt(): ?array
    {
        if (!$this->isList($this->value)) {
            return null;
        }

        foreach ($this->value as $value) {
            if (!is_int($value)) {
                return null;
            }
        }

        return $this->value;
    }

    /**
     * @psalm-pure
     * @return array|null
     */
    public function findListOfString(): ?array
    {
        if ($this->isList($this->value)) {
            return null;
        }

        foreach ($this->value as $value) {
            if (!is_string($value)) {
                return null;
            }
        }

        return $this->value;
    }

    /**
     * @psalm-pure
     * @return int|null
     */
    public function findAsInt(): ?int
    {
        return $this->castToInt($this->value);
    }

    /**
     * @psalm-pure
     * @return bool|null
     */
    public function findAsBool(): ?bool
    {
        return $this->castToBool($this->value);
    }

    /**
     * @psalm-pure
     * @return string|null
     */
    public function findAsString(): ?string
    {
        return $this->castToString($this->value);
    }

    /**
     * @psalm-return list<int>|null
     * @return array|null
     */
    public function findAsListOfInt(): ?array
    {
        if (!$this->isList($this->value)) {
            return null;
        }

        $listOfInt = [];
        foreach ($this->value as $value) {
            $intValue = $this->castToInt($value);
            if ($intValue === null) {
                return null;
            }

            $listOfInt[] = $intValue;
        }

        return $listOfInt;
    }

    /**
     * @psalm-return list<string>|null
     * @return array|null
     */
    public function findAsListOfString(): ?array
    {
        if (!$this->isList($this->value)) {
            return null;
        }

        $listOfString = [];
        foreach ($this->value as $value) {
            $stringValue = $this->castToInt($value);
            if ($stringValue === null) {
                return null;
            }

            $listOfString[] = $stringValue;
        }

        return $listOfString;
    }

    /**
     * @psalm-pure
     * @return mixed
     */
    public function getMixed()
    {
        return $this->value;
    }

    /**
     * @psalm-pure
     * @return mixed
     */
    public function findMixed()
    {
        return $this->value;
    }

    /**
     * @psalm-pure
     * @param string|int $offset
     * @return TypedAccessorInterface
     */
    public function offsetGet($offset)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         */
        if (!is_string($offset) && !is_int($offset)) {
            throw new UnexpectedKeyTypeException($offset, $this->keySequence);
        }

        $keySequence = $this->keySequence;
        $keySequence[] = $offset;

        if (!$this->offsetExists($offset)) {
            return new MissingValueAccessor($keySequence);
        }

        /**
         * @var array
         */
        $array = $this->value;

        return (new self($array[$offset]))->setKeySequence($keySequence);
    }

    /**
     * @psalm-pure
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (!is_array($this->value)) {
            return false;
        }

        return key_exists($offset, $this->value);
    }

    /**
     * @psalm-pure
     * @param $value
     * @return bool|null
     */
    private function castToBool($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        $intValue = $this->castToInt($value);
        if ($intValue === 1) {
            return true;
        }
        if ($intValue === 0) {
            return false;
        }

        return null;
    }

    /**
     * @psalm-pure
     * @param mixed $value
     * @return int|null
     */
    private function castToInt($value): ?int
    {
        if (is_bool($value)) {
            return null;
        }

        $intValue = filter_var($value, FILTER_VALIDATE_INT);
        if ($intValue === false) {
            return null;
        }

        return $intValue;
    }

    /**
     * @psalm-pure
     * @param $value
     * @return string|null
     */
    private function castToString($value): ?string
    {
        if (is_string($value)) {
            return $this->value;
        }

        if (is_int($this->value)) {
            return (string) $this->value;
        }

        return null;
    }

    /**
     * @psalm-pure
     * @param array $array
     * @return bool
     */
    private function isList(array $array): bool
    {
        if (!is_array($array)) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * @psalm-pure
     * @param array $keySequence
     * @return TypedAccessor
     */
    private function setKeySequence(array $keySequence): self
    {
        $this->keySequence = $keySequence;

        return $this;
    }

    /**
     * @psalm-pure
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new CannotModifyAccessorException($this->keySequence);
    }

    /**
     * @psalm-pure
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw new CannotModifyAccessorException($this->keySequence);
    }
}