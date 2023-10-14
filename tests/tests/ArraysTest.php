<?php
    namespace unique\yii2helperstest\tests;

    use PHPUnit\Framework\TestCase;
    use unique\yii2helpers\components\Arrays;

    class ArraysTest extends TestCase {

        public function testGetArrayValue() {

            $this->assertSame( [], Arrays::getArrayValue( [], '' ) );

            $this->assertSame( 1, Arrays::getArrayValue( [ 'a' => 1 ], 'a' ) );
            $this->assertSame( null, Arrays::getArrayValue( [ 'a' => 1 ], 'b' ) );

            $this->assertSame( [ 'b' => 1 ], Arrays::getArrayValue( [ 'a' => [ 'b' => 1 ], 'b' => 2 ], 'a' ) );
            $this->assertSame( [ 'b' => 1 ], Arrays::getArrayValue( [ 'a' => [ 'b' => 1 ], 'b' => 2 ], 'a.' ) );
            $this->assertSame( 1, Arrays::getArrayValue( [ 'a' => [ 'b' => 1 ], 'b' => 2 ], 'a.b' ) );
            $this->assertSame( null, Arrays::getArrayValue( [ 'a' => [ 'b' => 1 ], 'b' => 2 ], '.b' ) );
            $this->assertSame( null, Arrays::getArrayValue( [ 'a' => [ 'b' => null ], 'b' => 2 ], 'a.b' ) );
            $this->assertSame( null, Arrays::getArrayValue( [ 'a' => [ 'b' => 1 ], 'b' => 2 ], 'a.b.c' ) );
            $this->assertSame( null, Arrays::getArrayValue( [ 'a' => [ 'b' => 1 ], 'b' => 2 ], 'b.a' ) );
        }
    }