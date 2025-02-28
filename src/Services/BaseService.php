<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class BaseService
 *
 * This abstract class provides a base structure for services, including validation
 * and utility methods for handling data.
 *
 * @package App\Services
 */
abstract class BaseService
{
    /**
     * Get the validation rules that apply to the service.
     *
     * @return array<string, mixed>
     */
    abstract public function rules(): array;

    /**
     * Validate all data required to execute the service.
     *
     * @param array<string, mixed> $data The input data to validate.
     * @return bool Returns true if validation passes.
     * @throws ValidationException If validation fails.
     */
    public function validate(array $data): bool
    {
        Validator::make($data, $this->rules())->validate();
        return true;
    }

    /**
     * Returns the value if it exists and is not empty, otherwise returns null.
     *
     * @param array<string, mixed> $data The input data.
     * @param string $index The key to check in the data.
     * @return mixed|null The value or null if empty or not found.
     */
    public function nullOrValue(array $data, string $index): mixed
    {
        $value = Arr::get($data, $index);
        return empty($value) ? null : $value;
    }

    /**
     * Returns a Carbon date instance if the value exists and is not empty, otherwise returns null.
     *
     * @param array<string, mixed> $data The input data.
     * @param string $index The key to check in the data.
     * @return Carbon|null The parsed date or null if empty or not found.
     */
    public function nullOrDate(array $data, string $index): ?Carbon
    {
        $value = Arr::get($data, $index);
        return empty($value) ? null : Carbon::parse($value);
    }

    /**
     * Returns the value if it exists and is not empty, otherwise returns false.
     *
     * @param array<string, mixed> $data The input data.
     * @param string $index The key to check in the data.
     * @return mixed|false The value or false if empty or not found.
     */
    public function valueOrFalse(array $data, string $index): mixed
    {
        return $data[$index] ?? false;
    }

    /**
     * Returns the value if it exists and is not empty, otherwise returns true.
     *
     * @param array<string, mixed> $data The input data.
     * @param string $index The key to check in the data.
     * @return mixed|true The value or true if empty or not found.
     */
    public function valueOrTrue(array $data, string $index): mixed
    {
        return $data[$index] ?? true;
    }
}
