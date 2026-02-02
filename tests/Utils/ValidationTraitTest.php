<?php

namespace BrightleafDigital\Tests\Utils;

use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValidationTraitTest extends TestCase
{
    use ValidationTrait;

    /**
     * Test validateGid passes for valid GID.
     */
    public function testValidateGidPassesForValidGid(): void
    {
        // Should not throw
        $this->validateGid('12345', 'Task GID');
        $this->assertTrue(true); // Assert we got here
    }

    /**
     * Test validateGid throws for empty string.
     */
    public function testValidateGidThrowsForEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Task GID must be a non-empty string.');

        $this->validateGid('', 'Task GID');
    }

    /**
     * Test validateGid throws for whitespace-only string.
     */
    public function testValidateGidThrowsForWhitespaceOnly(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Project GID must be a non-empty string.');

        $this->validateGid('   ', 'Project GID');
    }

    /**
     * Test validateRequiredFields passes when all fields present.
     */
    public function testValidateRequiredFieldsPassesWhenPresent(): void
    {
        $data = ['name' => 'Task Name', 'workspace' => '12345'];

        // Should not throw
        $this->validateRequiredFields($data, ['name', 'workspace'], 'task creation');
        $this->assertTrue(true);
    }

    /**
     * Test validateRequiredFields throws for missing fields.
     */
    public function testValidateRequiredFieldsThrowsForMissingFields(): void
    {
        $data = ['name' => 'Task Name'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for task creation: workspace');

        $this->validateRequiredFields($data, ['name', 'workspace'], 'task creation');
    }

    /**
     * Test validateRequiredFields throws for empty string fields.
     */
    public function testValidateRequiredFieldsThrowsForEmptyString(): void
    {
        $data = ['name' => '', 'workspace' => '12345'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field(s) for task creation: name');

        $this->validateRequiredFields($data, ['name', 'workspace'], 'task creation');
    }

    /**
     * Test validateAtLeastOneField passes when one field present.
     */
    public function testValidateAtLeastOneFieldPassesWhenOnePresent(): void
    {
        $data = ['workspace' => '12345'];

        // Should not throw
        $this->validateAtLeastOneField($data, ['workspace', 'team'], 'project query');
        $this->assertTrue(true);
    }

    /**
     * Test validateAtLeastOneField throws when no fields present.
     */
    public function testValidateAtLeastOneFieldThrowsWhenNonePresent(): void
    {
        $data = ['other' => 'value'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'At least one of the following fields is required for project query: workspace, team'
        );

        $this->validateAtLeastOneField($data, ['workspace', 'team'], 'project query');
    }

    /**
     * Test validateDateFormat passes for valid date.
     */
    public function testValidateDateFormatPassesForValidDate(): void
    {
        // Should not throw
        $this->validateDateFormat('2024-12-31', 'due_on');
        $this->assertTrue(true);
    }

    /**
     * Test validateDateFormat throws for invalid format.
     */
    public function testValidateDateFormatThrowsForInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('due_on must be in YYYY-MM-DD format.');

        $this->validateDateFormat('12/31/2024', 'due_on');
    }

    /**
     * Test validateColor passes for valid color.
     */
    public function testValidateColorPassesForValidColor(): void
    {
        // Should not throw
        $this->validateColor('dark-blue');
        $this->validateColor('light-green');
        $this->validateColor('none');
        $this->assertTrue(true);
    }

    /**
     * Test validateColor throws for invalid color.
     */
    public function testValidateColorThrowsForInvalidColor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid color "red".');

        $this->validateColor('red');
    }

    /**
     * Test validateLimit passes for valid limit.
     */
    public function testValidateLimitPassesForValidLimit(): void
    {
        // Should not throw
        $this->validateLimit(50);
        $this->validateLimit(1);
        $this->validateLimit(100);
        $this->assertTrue(true);
    }

    /**
     * Test validateLimit throws for limit below minimum.
     */
    public function testValidateLimitThrowsForBelowMinimum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100.');

        $this->validateLimit(0);
    }

    /**
     * Test validateLimit throws for limit above maximum.
     */
    public function testValidateLimitThrowsForAboveMaximum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100.');

        $this->validateLimit(101);
    }

    /**
     * Test validateGidArray passes for valid array.
     */
    public function testValidateGidArrayPassesForValidArray(): void
    {
        // Should not throw
        $this->validateGidArray(['12345', '67890'], 'followers');
        $this->assertTrue(true);
    }

    /**
     * Test validateGidArray throws for empty array.
     */
    public function testValidateGidArrayThrowsForEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('followers must be a non-empty array.');

        $this->validateGidArray([], 'followers');
    }

    /**
     * Test validateGidArray throws for invalid element.
     */
    public function testValidateGidArrayThrowsForInvalidElement(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('members[1] must be a non-empty string.');

        $this->validateGidArray(['12345', ''], 'members');
    }
}
