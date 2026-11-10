<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\MaterialGroup;

class MaterialGroupTemplateTest extends TestCase
{
    public function test_it_identifies_empty_and_null_template()
    {
        $group = new MaterialGroup();
        $this->assertFalse($group->hasSpecificationTemplate());
        $this->assertFalse($group->validateSpecification('any specification'));

        $group->specification_template = '   ';
        $this->assertFalse($group->hasSpecificationTemplate());
        $this->assertFalse($group->validateSpecification('any specification'));
    }

    public function test_it_parses_template_with_mixed_types_and_statics()
    {
        $group = new MaterialGroup();
        $group->specification_template = '[Width:number] mm x [Length:number] mm - [Color:text]';

        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(5, $segments);

        $this->assertEquals(['type' => 'variable', 'label' => 'Width', 'datatype' => 'number'], $segments[0]);
        $this->assertEquals(['type' => 'static', 'value' => ' mm x '], $segments[1]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Length', 'datatype' => 'number'], $segments[2]);
        $this->assertEquals(['type' => 'static', 'value' => ' mm - '], $segments[3]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Color', 'datatype' => 'text'], $segments[4]);

        $this->assertTrue($group->validateSpecification('10 mm x 20 mm - Red'));
        $this->assertTrue($group->validateSpecification('10.5 mm x 20.25 mm - Matte Black'));

        // Invalid: number contains non-numeric
        $this->assertFalse($group->validateSpecification('ten mm x 20 mm - Red'));

        // Invalid: missing static delimiter
        $this->assertFalse($group->validateSpecification('10mm x 20 mm - Red'));
    }

    public function test_it_handles_default_text_type_when_unspecified()
    {
        $group = new MaterialGroup();
        $group->specification_template = '[Packaging] of [Count:number] pcs';

        $segments = $group->parseSpecificationTemplate();
        $this->assertEquals(['type' => 'variable', 'label' => 'Packaging', 'datatype' => 'text'], $segments[0]);
        $this->assertEquals(['type' => 'static', 'value' => ' of '], $segments[1]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Count', 'datatype' => 'number'], $segments[2]);
        $this->assertEquals(['type' => 'static', 'value' => ' pcs'], $segments[3]);

        $this->assertTrue($group->validateSpecification('Box of 100 pcs'));
        $this->assertFalse($group->validateSpecification('Box of abc pcs'));
        $this->assertFalse($group->validateSpecification('Box of 100 pcs extra'));
    }

    public function test_it_handles_special_regex_characters_in_static_parts()
    {
        $group = new MaterialGroup();
        $group->specification_template = '[Diameter:number]mm (Grade: [Grade:text]) [Note:text]';

        $this->assertTrue($group->validateSpecification('16mm (Grade: 60) High Tensile'));
        $this->assertFalse($group->validateSpecification('16mm Grade: 60 High Tensile'));
    }

    public function test_it_handles_escaped_brackets_in_template()
    {
        $group = new MaterialGroup();
        $group->specification_template = '\[Grade\] [Size:number]mm';

        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(3, $segments);
        $this->assertEquals(['type' => 'static', 'value' => '[Grade] '], $segments[0]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Size', 'datatype' => 'number'], $segments[1]);
        $this->assertEquals(['type' => 'static', 'value' => 'mm'], $segments[2]);

        $this->assertTrue($group->validateSpecification('[Grade] 16mm'));
        $this->assertFalse($group->validateSpecification('16mm'));
        $this->assertFalse($group->validateSpecification('Grade 16mm'));
    }

    public function test_it_handles_escaped_brackets_enclosing_variables()
    {
        $group = new MaterialGroup();
        $group->specification_template = '\[[Count:number]\]';

        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(3, $segments);
        $this->assertEquals(['type' => 'static', 'value' => '['], $segments[0]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Count', 'datatype' => 'number'], $segments[1]);
        $this->assertEquals(['type' => 'static', 'value' => ']'], $segments[2]);

        $this->assertTrue($group->validateSpecification('[50]'));
        $this->assertFalse($group->validateSpecification('50'));
        $this->assertFalse($group->validateSpecification('[abc]'));
    }

    public function test_it_handles_mixed_escaped_brackets_and_escaped_backslash()
    {
        $group = new MaterialGroup();
        $group->specification_template = 'Box \[ [Count:number] \] pcs - \[Special\]';

        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(3, $segments);
        $this->assertEquals(['type' => 'static', 'value' => 'Box [ '], $segments[0]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Count', 'datatype' => 'number'], $segments[1]);
        $this->assertEquals(['type' => 'static', 'value' => ' ] pcs - [Special]'], $segments[2]);

        $this->assertTrue($group->validateSpecification('Box [ 100 ] pcs - [Special]'));
        $this->assertFalse($group->validateSpecification('Box 100 pcs - [Special]'));
        $this->assertFalse($group->validateSpecification('Box [ 100 ] pcs - Special'));

        // Test escaped backslash
        $group->specification_template = '\\\\[Width:number]\]';
        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(3, $segments);
        $this->assertEquals(['type' => 'static', 'value' => '\\'], $segments[0]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Width', 'datatype' => 'number'], $segments[1]);
        $this->assertEquals(['type' => 'static', 'value' => ']'], $segments[2]);
        $this->assertTrue($group->validateSpecification('\\20]'));
    }

    public function test_it_handles_escaped_brackets_in_variable_label()
    {
        $group = new MaterialGroup();
        $group->specification_template = '[Width \[mm\]:number] x [Length \[mm\]:number]';

        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(3, $segments);
        $this->assertEquals(['type' => 'variable', 'label' => 'Width [mm]', 'datatype' => 'number'], $segments[0]);
        $this->assertEquals(['type' => 'static', 'value' => ' x '], $segments[1]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Length [mm]', 'datatype' => 'number'], $segments[2]);

        $this->assertTrue($group->validateSpecification('10 x 20'));
    }

    public function test_it_handles_fractional_and_mixed_numbers_in_numeric_variables()
    {
        $group = new MaterialGroup();
        $group->specification_template = '[Diameter:number]" \[ [Unit:text] \]';

        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(4, $segments);
        $this->assertEquals(['type' => 'variable', 'label' => 'Diameter', 'datatype' => 'number'], $segments[0]);
        $this->assertEquals(['type' => 'static', 'value' => '" [ '], $segments[1]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Unit', 'datatype' => 'text'], $segments[2]);
        $this->assertEquals(['type' => 'static', 'value' => ' ]'], $segments[3]);

        // Fractions like 1/2 and 3/4
        $this->assertTrue($group->validateSpecification('1/2" [ pc ]'));
        $this->assertTrue($group->validateSpecification('3/4" [ pc ]'));

        // Mixed numbers like 1 1/2 and 1-1/2
        $this->assertTrue($group->validateSpecification('1 1/2" [ pc ]'));
        $this->assertTrue($group->validateSpecification('1-1/2" [ pc ]'));

        // Standard integers and decimals
        $this->assertTrue($group->validateSpecification('10" [ pc ]'));
        $this->assertTrue($group->validateSpecification('10.5" [ pc ]'));

        // Invalid non-numeric values
        $this->assertFalse($group->validateSpecification('abc" [ pc ]'));
        $this->assertFalse($group->validateSpecification('ten" [ pc ]'));
    }

    public function test_it_handles_integer_and_float_datatypes()
    {
        $group = new MaterialGroup();
        $group->specification_template = '[Quantity:integer] pcs - [Thickness:float] mm';

        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(4, $segments);
        $this->assertEquals(['type' => 'variable', 'label' => 'Quantity', 'datatype' => 'integer'], $segments[0]);
        $this->assertEquals(['type' => 'static', 'value' => ' pcs - '], $segments[1]);
        $this->assertEquals(['type' => 'variable', 'label' => 'Thickness', 'datatype' => 'float'], $segments[2]);
        $this->assertEquals(['type' => 'static', 'value' => ' mm'], $segments[3]);

        // Valid: integer only and float with decimal
        $this->assertTrue($group->validateSpecification('50 pcs - 1.5 mm'));
        $this->assertTrue($group->validateSpecification('0 pcs - 0.75 mm'));
        $this->assertTrue($group->validateSpecification('100 pcs - .5 mm'));

        // Invalid: float without decimal point
        $this->assertFalse($group->validateSpecification('50 pcs - 2 mm'));

        // Invalid: integer with decimal
        $this->assertFalse($group->validateSpecification('50.5 pcs - 1.5 mm'));

        // Invalid: non-numeric
        $this->assertFalse($group->validateSpecification('abc pcs - 1.5 mm'));
    }

    public function test_it_handles_enum_datatype_with_options()
    {
        $group = new MaterialGroup();
        $group->specification_template = '[Size:enum:Small|Medium|Large] - [Color:enum:Red|Blue]';

        $segments = $group->parseSpecificationTemplate();
        $this->assertCount(3, $segments);
        $this->assertEquals([
            'type' => 'variable',
            'label' => 'Size',
            'datatype' => 'enum',
            'options' => ['Small', 'Medium', 'Large']
        ], $segments[0]);
        $this->assertEquals(['type' => 'static', 'value' => ' - '], $segments[1]);
        $this->assertEquals([
            'type' => 'variable',
            'label' => 'Color',
            'datatype' => 'enum',
            'options' => ['Red', 'Blue']
        ], $segments[2]);

        // Valid matching values
        $this->assertTrue($group->validateSpecification('Small - Red'));
        $this->assertTrue($group->validateSpecification('Medium - Blue'));
        $this->assertTrue($group->validateSpecification('Large - Red'));

        // Invalid: option not in enum list
        $this->assertFalse($group->validateSpecification('Extra-Large - Red'));
        $this->assertFalse($group->validateSpecification('Small - Green'));
        $this->assertFalse($group->validateSpecification('small - Red')); // Case mismatch
    }
}
