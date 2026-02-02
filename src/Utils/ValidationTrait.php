<?php

namespace BrightleafDigital\Utils;

use InvalidArgumentException;

/**
 * Trait providing common validation methods for API services.
 *
 * This trait provides reusable validation methods to ensure input parameters
 * are valid before making API requests, improving error messages and preventing
 * unnecessary API calls with invalid data.
 */
trait ValidationTrait
{
    /**
     * Validate that a GID parameter is a non-empty string.
     *
     * @param string $gid The GID to validate.
     * @param string $parameterName The name of the parameter for the error message.
     *
     * @throws InvalidArgumentException If the GID is empty or not a valid format.
     */
    protected function validateGid(string $gid, string $parameterName): void
    {
        if (trim($gid) === '') {
            throw new InvalidArgumentException(
                sprintf('%s must be a non-empty string.', $parameterName)
            );
        }
    }

    /**
     * Validate that required fields are present in the data array.
     *
     * @param array $data The data array to validate.
     * @param array $requiredFields The list of required field names.
     * @param string $context The context for the error message (e.g., "task creation").
     *
     * @throws InvalidArgumentException If any required field is missing.
     */
    protected function validateRequiredFields(array $data, array $requiredFields, string $context): void
    {
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Missing required field(s) for %s: %s',
                    $context,
                    implode(', ', $missingFields)
                )
            );
        }
    }

    /**
     * Validate that at least one of the specified fields is present in the data array.
     *
     * @param array $data The data array to validate.
     * @param array $fields The list of field names where at least one must be present.
     * @param string $context The context for the error message.
     *
     * @throws InvalidArgumentException If none of the specified fields are present.
     */
    protected function validateAtLeastOneField(array $data, array $fields, string $context): void
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && (!is_string($data[$field]) || trim($data[$field]) !== '')) {
                return; // At least one field is present
            }
        }

        throw new InvalidArgumentException(
            sprintf(
                'At least one of the following fields is required for %s: %s',
                $context,
                implode(', ', $fields)
            )
        );
    }

    /**
     * Validate that a date string is in YYYY-MM-DD format.
     *
     * @param string $date The date string to validate.
     * @param string $parameterName The name of the parameter for the error message.
     *
     * @throws InvalidArgumentException If the date is not in the correct format.
     */
    protected function validateDateFormat(string $date, string $parameterName): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException(
                sprintf('%s must be in YYYY-MM-DD format.', $parameterName)
            );
        }
    }

    /**
     * Validate that a value is a valid Asana color.
     *
     * @param string $color The color value to validate.
     *
     * @throws InvalidArgumentException If the color is not a valid Asana color.
     */
    protected function validateColor(string $color): void
    {
        $validColors = [
            'dark-pink', 'dark-green', 'dark-blue', 'dark-red', 'dark-teal',
            'dark-brown', 'dark-orange', 'dark-purple', 'dark-warm-gray',
            'light-pink', 'light-green', 'light-blue', 'light-red', 'light-teal',
            'light-brown', 'light-orange', 'light-purple', 'light-warm-gray',
            'none'
        ];

        if (!in_array($color, $validColors, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid color "%s". Valid colors are: %s',
                    $color,
                    implode(', ', $validColors)
                )
            );
        }
    }

    /**
     * Validate that a limit value is within acceptable range.
     *
     * @param int $limit The limit value to validate.
     * @param int $min The minimum allowed value (default: 1).
     * @param int $max The maximum allowed value (default: 100).
     *
     * @throws InvalidArgumentException If the limit is outside the acceptable range.
     */
    protected function validateLimit(int $limit, int $min = 1, int $max = 100): void
    {
        if ($limit < $min || $limit > $max) {
            throw new InvalidArgumentException(
                sprintf('Limit must be between %d and %d.', $min, $max)
            );
        }
    }

    /**
     * Validate an array of GIDs.
     *
     * @param array $gids The array of GIDs to validate.
     * @param string $parameterName The name of the parameter for the error message.
     *
     * @throws InvalidArgumentException If the array is empty or contains invalid GIDs.
     */
    protected function validateGidArray(array $gids, string $parameterName): void
    {
        if (empty($gids)) {
            throw new InvalidArgumentException(
                sprintf('%s must be a non-empty array.', $parameterName)
            );
        }

        foreach ($gids as $index => $gid) {
            if (!is_string($gid) || trim($gid) === '') {
                throw new InvalidArgumentException(
                    sprintf('%s[%d] must be a non-empty string.', $parameterName, $index)
                );
            }
        }
    }
}
