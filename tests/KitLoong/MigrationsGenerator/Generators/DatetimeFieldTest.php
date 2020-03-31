<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 11:42
 */

namespace Tests\KitLoong\MigrationsGenerator\Generators;

use Doctrine\DBAL\Schema\Column;
use KitLoong\MigrationsGenerator\Generators\DatetimeField;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnModifier;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnName;
use KitLoong\MigrationsGenerator\MigrationMethod\ColumnType;
use Mockery;
use Orchestra\Testbench\TestCase;

class DatetimeFieldTest extends TestCase
{
    public function testMakeFieldIsSoftDeletes()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $field = [
            'field' => ColumnName::DELETED_AT,
            'type' => 'timestamp',
            'args' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getNotnull')
            ->andReturn(false)
            ->once();
        $column->shouldReceive('getLength')
            ->andReturn(2);

        $field = $datetimeField->makeField($field, $column, false);
        $this->assertSame(ColumnType::SOFT_DELETES, $field['type']);
        $this->assertNull($field['field']);
        $this->assertSame(["'".ColumnName::DELETED_AT."'", 2], $field['args']);
    }

    public function testMakeFieldIsTimestamps()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $field = [
            'field' => ColumnName::UPDATED_AT,
            'type' => 'timestamp',
            'args' => []
        ];

        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getLength')
            ->andReturn(2);

        $field = $datetimeField->makeField($field, $column, true);
        $this->assertSame(ColumnType::TIMESTAMPS, $field['type']);
        $this->assertNull($field['field']);
        $this->assertSame([2], $field['args']);
    }

    public function testMakeFieldSkipCreatedAtWhenIsTimestamps()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $field = [
            'field' => ColumnName::CREATED_AT,
            'type' => 'timestamp',
            'args' => []
        ];
        $column = Mockery::mock(Column::class);
        $field = $datetimeField->makeField($field, $column, true);
        $this->assertEmpty($field);
    }

    public function testMakeDefaultIsUseCurrent()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);
        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getDefault')
            ->andReturn('CURRENT_TIMESTAMP')
            ->once();

        $result = $datetimeField->makeDefault($column);
        $this->assertSame(ColumnModifier::USE_CURRENT, $result);
    }

    public function testMakeDefaulNormal()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);
        $column = Mockery::mock(Column::class);
        $column->shouldReceive('getDefault')
            ->andReturn('Default');

        $result = $datetimeField->makeDefault($column);
        $this->assertSame("default('Default')", $result);
    }

    public function testIsUseTimestamps()
    {
        /** @var DatetimeField $datetimeField */
        $datetimeField = resolve(DatetimeField::class);

        $columnCreatedAt = Mockery::mock(Column::class);
        $columnCreatedAt->shouldReceive('getName')
            ->andReturn(ColumnName::CREATED_AT);
        $columnCreatedAt->shouldReceive('getNotnull')
            ->andReturn(false);
        $columnCreatedAt->shouldReceive('getDefault')
            ->andReturn(false);

        $columnUpdatedAt = Mockery::mock(Column::class);
        $columnUpdatedAt->shouldReceive('getName')
            ->andReturn(ColumnName::UPDATED_AT);
        $columnUpdatedAt->shouldReceive('getNotnull')
            ->andReturn(false);
        $columnUpdatedAt->shouldReceive('getDefault')
            ->andReturn(false);

        $this->assertFalse($datetimeField->isUseTimestamps([$columnUpdatedAt, $columnCreatedAt]));
    }
}